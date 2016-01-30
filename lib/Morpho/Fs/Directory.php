<?php
declare(strict_types = 1);

namespace Morpho\Fs;

use Morpho\Base\NotImplementedException;
use Morpho\Base\ArrayTool;
use DirectoryIterator;

class Directory extends Entry {
    const FILE = 0x01;
    const DIR = 0x02;

    public static function move(string $sourceDirPath, string $targetDirPath) {
        self::copy($sourceDirPath, $targetDirPath);
        self::delete($sourceDirPath);
    }

    public static function copy(string $sourceDirPath, string $targetDirPath, $processor = null, array $options = null) {
        // @TODO: Handle $options
        // @TODO: Handle the case: cp module/system ../../dst/module should create ../../dst/module/system
        // @TODO: Handle dots and relative paths: '..', '.' a
        self::ensureExists($sourceDirPath);
        if (!is_dir($sourceDirPath)) {
            throw new IoException("Source path must be a directory.");
        }
        if ($sourceDirPath === $targetDirPath) {
            throw new IoException("Cannot copy a directory '$sourceDirPath' into itself.");
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
     * @TODO: Return \Generator
     */
    public static function listEntries($dirPaths, $processor = null, array $options = []): array {
        if (null !== $processor && !is_string($processor) && !$processor instanceof \Closure) {
            throw new IoException();
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

        $paths = [];
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
                        $paths[] = $path;
                    }

                    if ($options['recursive']) {
                        if ($item->isLink() && !$options['followSymlinks']) {
                            continue;
                        }
                        $paths = array_merge(
                            $paths,
                            self::listEntries($item->getPathname(), $processor, $options)
                        );
                    }
                } else {
                    if ($options['type'] & self::FILE) {
                        $paths[] = $path;
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * Shortcut for the listEntries() with $options['type'] == self::DIR option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     *     // @TODO: Return \Generator
     */
    public static function listDirs($dirPath, $processor = null, array $options = []): array {
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
     * // @TODO: Return \Generator
     */
    public static function listFiles($dirPath, $processor = null, array $options = []): array {
        $options['type'] = self::FILE;
        return self::listEntries($dirPath, $processor, $options);
    }

    // @TODO: Return \Generator
    public static function listLinks(string $dirPath, $processor = null): array {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @TODO: Extract listLinks() method, use it with $processor that will check link and
     * return true for broken links.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function listBrokenLinks($dirPath, $processor = null): array {
        $brokenLinkPaths = [];
        foreach (Directory::listEntries($dirPath, $processor) as $path) {
            if (is_link($path)) {
                $targetPath = readlink($path);
                if (false === $targetPath || !self::isEntry($path)) {
                    $brokenLinkPaths[$path] = $targetPath;
                }
            }
        }
        return $brokenLinkPaths;
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
     * Deletes files and directories recursively from a file system
     *
     * This method recursively removes the $dirPath and all its contents.
     * You should be extremely careful with this method as it has the
     * potential to erase everything that the current user has access to.
     *
     * The base for this method was taken from the eZ Components, ezcBaseFile::removeRecursive()
     */
    public static function delete(string $dirPath, bool $deleteSelf = true) {
        self::ensureExists($dirPath);
        $sourceDirPath = realpath($dirPath);
        if (!$sourceDirPath) {
            throw new IoException("The directory '$dirPath' could not be found.");
        }
        $d = @dir($sourceDirPath);
        if (!$d) {
            throw new IoException("The directory '$dirPath' can not be opened for reading.");
        }
        // Check if we can delete the dir.
        if (!is_writable(realpath($dirPath . '/' . '..'))) {
            throw new IoException("The directory '$dirPath' can not be opened for writing.");
        }
        // Loop over contents.
        while (($fileName = $d->read()) !== false) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }
            $filePath = $sourceDirPath . '/' . $fileName;
            if (is_dir($filePath)) {
                self::delete($filePath);
            } else {
                if (false === @unlink($filePath)) {
                    $message = "The file '$filePath' can not be deleted";
                    $error = error_get_last();
                    if (preg_match('~unlink\(.*\): Permission denied~s', $error['message'])) {
                        $message .= ': permission denied.';
                    } else {
                        $message .= '.';
                    }
                    throw new IoException($message);
                }
            }
        }
        $d->close();
        if ($deleteSelf) {
            $success = @rmdir($sourceDirPath);
            if (!$success) {
                throw new IoException("Unable to delete the directory '$sourceDirPath': permission denied.");
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
            throw new IoException("Unable to generate an unique path for the directory '$dirPath' (tried $i times).");
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
            throw new IoException("The directory path is empty.");
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
                throw new IoException($message . ' ' . $error['message']);
            } else {
                throw new IoException($message);
            }
        }

        umask($oldUmask);

        return $dirPath;
    }

    public static function isEntry(string $path): bool {
        return is_file($path) || is_dir($path) || is_link($path);
    }

    public static function ensureExists(string $dirPath) {
        if (!is_dir($dirPath) || empty($dirPath)) {
            throw new IoException("The '$dirPath' directory does not exist.");
        }
    }
}