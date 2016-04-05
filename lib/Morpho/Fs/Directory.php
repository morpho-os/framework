<?php
declare(strict_types = 1);

namespace Morpho\Fs;

use Morpho\Base\NotImplementedException;
use Morpho\Base\ArrayTool;
use DirectoryIterator;

class Directory extends Entry {
    const FILE = 0x01;
    const DIR = 0x02;

    public static function move(string $sourceDirPath, string $targetDirPath)/*: void */ {
        self::copy($sourceDirPath, $targetDirPath);
        self::delete($sourceDirPath);
    }

    public static function copy(string $sourceDirPath, string $targetDirPath, $processor = null, array $options = null)/*: void */ {
        // @TODO: Handle $options
        // @TODO: Handle the case: cp module/system ../../dst/module should create ../../dst/module/system
        // @TODO: Handle dots and relative paths: '..', '.' a
        self::ensureExists($sourceDirPath);
        if (!is_dir($sourceDirPath)) {
            throw new Exception("Source path must be a directory.");
        }
        if ($sourceDirPath === $targetDirPath) {
            throw new Exception("Cannot copy a directory '$sourceDirPath' into itself.");
        }

        if (is_dir($targetDirPath)) {
            $targetDirPath .= '/' . basename($sourceDirPath);
        }

        $targetDirPath = self::create($targetDirPath, self::mode($sourceDirPath));

        $paths = self::listEntries(
            $sourceDirPath,
            $processor,
            [
                'recursive' => false,
                'type'      => self::FILE | self::DIR,
            ]
        );
        foreach ($paths as $sourceFilePath) {
            $targetPath = $targetDirPath . '/' . basename($sourceFilePath);
            if (is_file($sourceFilePath) || is_link($sourceFilePath)) {
                File::copy($sourceFilePath, $targetPath, false, false);
            } else {
                self::copy($sourceFilePath, $targetPath, $processor);
            }
        }
    }

    /**
     * @param array|string $dirPaths
     * @param string|\Closure $processor
     */
    public static function listEntries($dirPaths, $processor = null, array $options = []): \Generator {
        if (null !== $processor && !is_string($processor) && !$processor instanceof \Closure) {
            throw new Exception();
        }
        $options = ArrayTool::handleOptions(
            $options,
            [
                'recursive'      => true,
                'followSymlinks' => false,
                'type'           => self::FILE | self::DIR,
            ]
        );

        if (is_string($processor)) {
            $regexp = $processor;
            $processor = function ($path, $isDir) use ($regexp) {
                return $isDir || preg_match($regexp, $path);
            };
        }

        foreach ((array)$dirPaths as $dirPath) {
            foreach (new DirectoryIterator($dirPath) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $path = str_replace('\\', '/', $item->getPathname());
                $isDir = $item->isDir();

                if (null !== $processor && !$processor($path, $isDir)) {
                    continue;
                }

                if ($isDir) {
                    if ($options['type'] & self::DIR) {
                        yield $path;
                    }

                    if ($options['recursive']) {
                        if ($item->isLink() && !$options['followSymlinks']) {
                            continue;
                        }

                        yield from self::listEntries($item->getPathname(), $processor, $options);
                    }
                } else {
                    if ($options['type'] & self::FILE) {
                        yield $path;
                    }
                }
            }
        }
    }

    /**
     * Shortcut for the listEntries() with $options['type'] == self::DIR option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function listDirs($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = self::DIR;
        if (is_string($processor)) {
            $regexp = $processor;
            $processor = function ($path) use ($regexp) {
                return preg_match($regexp, $path);
            };
        }
        return self::listEntries($dirPath, $processor, $options);
    }

    public static function listEmptyDirs($dirPath, $processor = null): \Generator {
        // @TODO
        throw new NotImplementedException();
    }

    /**
     * Shortcut for the listEntries() with $options['type'] == self::FILE option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function listFiles($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = self::FILE;
        return self::listEntries($dirPath, $processor, $options);
    }

    public static function listFilesWithExt($dirPath, array $extensions, array $options = []): \Generator {
        foreach ($extensions as $k => $extension) {
            $extensions[$k] = preg_quote($extension, '/');
        }
        return self::listFiles($dirPath, '/\.(' . implode('|', $extensions) . ')$/si', $options);
    }

    public static function listLinks(string $dirPath, $processor = null): \Generator {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @TODO: Extract listLinks() method, use it with $processor that will check link and
     * return true for broken links.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function listBrokenLinks($dirPath, $processor = null): \Generator {
        foreach (Directory::listEntries($dirPath, $processor) as $path) {
            if (is_link($path)) {
                $targetPath = readlink($path);
                if (false === $targetPath || !self::isEntry($path)) {
                    yield $path => $targetPath;
                }
            }
        }
    }

    public static function tmpDirPath(): string {
        return Path::normalize(sys_get_temp_dir());
    }

    /**
     * @return Path to the created directory.
     */
    public static function createTmpDir(string $relativeDirPath, int $mode = 0755): string {
        return self::create(
            Path::combine(self::tmpDirPath(), $relativeDirPath),
            $mode
        );
    }

    /**
     * Deletes files and directories recursively from a file system.
     *
     * This method recursively removes the $dirPath and all its contents.
     * You should be extremely careful with this method as it has the
     * potential to erase everything that the current user has access to.
     *
     * This method uses code which was found in eZ Components (ezcBaseFile::removeRecursive() method).
     */
    public static function delete(string $dirPath, bool $deleteSelf = true)/*: void */ {
        self::ensureExists($dirPath);
        $absFilePath = realpath($dirPath);
        if (!$absFilePath) {
            throw new Exception("The directory '$dirPath' could not be found.");
        }
        $d = @dir($absFilePath);
        if (!$d) {
            throw new Exception("The directory '$dirPath' can not be opened for reading.");
        }
        // Check if we can delete the dir.
        if (!is_writable(realpath($dirPath . '/' . '..'))) {
            throw new Exception("The directory '$dirPath' can not be opened for writing.");
        }
        // Loop over contents.
        while (($fileName = $d->read()) !== false) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }
            $filePath = $absFilePath . '/' . $fileName;
            if (is_dir($filePath)) {
                self::delete($filePath);
            } else {
                if (false === @unlink($filePath)) {
                    $message = "The file '$filePath' can not be deleted";
                    $error = error_get_last();
                    error_clear_last();
                    if (preg_match('~unlink\(.*\): Permission denied~s', $error['message'])) {
                        $message .= ': permission denied.';
                    } else {
                        $message .= '.';
                    }
                    throw new Exception($message);
                }
            }
        }
        $d->close();
        if ($deleteSelf) {
            $success = @rmdir($absFilePath);
            if (!$success) {
                throw new Exception("Unable to delete the directory '$absFilePath': permission denied.");
            }
        }
    }

    /**
     * Generates unique path for directory if the directory with
     * the given path already exists.
     */
    public static function uniquePath(string $dirPath, int $numberOfAttempts = 1000): string {
        $uniquePath = $dirPath;
        for ($i = 0; is_dir($uniquePath) && $i < $numberOfAttempts; $i++) {
            $uniquePath = $dirPath . '-' . $i;
        }
        if ($i == $numberOfAttempts && is_dir($uniquePath)) {
            throw new Exception("Unable to generate an unique path for the directory '$dirPath' (tried $i times).");
        }

        return $uniquePath;
    }

    public static function recreate(string $dirPath, int $mode = 0755, bool $recursive = true): string {
        if (is_dir($dirPath)) {
            self::delete($dirPath);
        }
        self::create($dirPath, $mode, $recursive);

        return $dirPath;
    }

    public static function create(string $dirPath, int $mode = 0755, bool $recursive = true): string {
        if (empty($dirPath)) {
            throw new Exception("The directory path is empty.");
        }

        if (is_dir($dirPath)) {
            return $dirPath;
        }

        $oldUmask = umask(0);

        if (!@mkdir($dirPath, $mode, $recursive)) {
            umask($oldUmask);
            $error = error_get_last();
            $message = "Unable to create the directory '$dirPath' with mode: $mode.";
            if (null !== $error) {
                throw new Exception($message . ' ' . $error['message']);
            } else {
                throw new Exception($message);
            }
        }

        umask($oldUmask);

        return $dirPath;
    }

    public static function isEntry(string $path): bool {
        return is_file($path) || is_dir($path) || is_link($path);
    }

    /**
     * @TODO: Rename
     */
    public static function ensureExists(string $dirPath)/*: void */ {
        if (!is_dir($dirPath) || empty($dirPath)) {
            throw new Exception("The '$dirPath' directory does not exist.");
        }
    }
}