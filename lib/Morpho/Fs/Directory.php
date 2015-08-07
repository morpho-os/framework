<?php
namespace Morpho\Fs;

use Morpho\Base\NotImplementedException;
use DirectoryIterator;
use Morpho\Base\ArrayTool;

class Directory extends Entry {
    const FILE = 0x01;
    const DIR = 0x02;

    public static function move($sourceDirPath, $targetDirPath) {
        self::copy($sourceDirPath, $targetDirPath);
        self::delete($sourceDirPath);
    }

    /**
     * @param $sourceDirPath
     * @param $targetDirPath
     * @param string|\Closure $processor
     */
    public static function copy($sourceDirPath, $targetDirPath, $processor = null) {
        // @TODO: Handle the case: cp module/system ../../dst/module should create ../../dst/module/system
        self::ensureDirExists($sourceDirPath);
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
                'type' => self::FILE | self::DIR,
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
     * @param array $options
     *
     * @throws IoException
     * @return array Returns an array of paths.
     */
    public static function listEntries($dirPaths, $processor = null, array $options = []) {
        if (null !== $processor && !is_string($processor) && !$processor instanceof \Closure) {
            throw new IoException();
        }
        $options = ArrayTool::handleOptions(
            $options,
            [
                'recursive' => true,
                'followSymlinks' => false,
                'type' => self::FILE | self::DIR,
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
     * @param array $options
     *
     * @return array Returns an array of paths.
     */
    public static function listDirs($dirPath, $processor = null, array $options = []) {
        $options['type'] = self::DIR;
        if (is_string($processor)) {
            $regexp = $processor;
            $processor = function ($path) use ($regexp) {
                return preg_match($regexp, $path);
            };
        }
        return self::listEntries($dirPath, $processor, $options);
    }

    public static function listEmptyDirs($dirPath, $processor = null) {
        // @TODO
        throw new NotImplementedException();
    }

    /**
     * Shortcut for the listEntries() with $options['type'] == self::FILE option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     * @param array $options
     *
     * @return array Returns an array of paths.
     */
    public static function listFiles($dirPath, $processor = null, array $options = []) {
        $options['type'] = self::FILE;
        return self::listEntries($dirPath, $processor, $options);
    }

    public static function listLinks($dirPath, $processor = null) {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @TODO: Extract listLinks() method, use it with $processor that will check link and
     * return true for broken links.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     * @return array
     */
    public static function listBrokenLinks($dirPath, $processor = null) {
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

    /**
     * @return string
     */
    public static function tmpDirPath() {
        return Path::normalize(sys_get_temp_dir());
    }

    /**
     * @param string $dirPath Relative path that will be combined with system temp path.
     * @param int $mode
     * @return string Path to the created directory.
     */
    public static function createTmpDir($dirPath, $mode = 0755) {
        return self::create(Path::combine(self::tmpDirPath(), $dirPath), $mode);
    }

    /**
     * Deletes files and directories recursively from a file system
     *
     * This method recursively removes the $dirPath and all its contents.
     * You should be extremely careful with this method as it has the
     * potential to erase everything that the current user has access to.
     *
     * The source for this method was found in the eZ Components, ezcBaseFile::removeRecursive() method,
     * after that it adopted (changed) to match our needs.
     *
     * @param string $dirPath
     * @param bool $deleteSelf
     * @throws IoException
     */
    public static function delete($dirPath, $deleteSelf = true) {
        self::ensureDirExists($dirPath);

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
     * @param string $dirPath
     * @param int $numberOfAttempts
     * @return string
     */
    public static function uniquePath($dirPath, $numberOfAttempts = 1000) {
        $uniquePath = $dirPath;
        for ($i = 0; is_dir($uniquePath) && $i < $numberOfAttempts; $i++) {
            $uniquePath = $dirPath . '-' . $i;
        }
        if ($i == $numberOfAttempts && is_dir($uniquePath)) {
            throw new IoException("Unable to generate an unique path for the directory '$dirPath' (tried $i times).");
        }

        return $uniquePath;
    }

    /**
     * @param string $dirPath
     * @param int $mode
     * @param bool $recursive
     * @return string
     */
    public static function recreate($dirPath, $mode = 0755, $recursive = true) {
        if (is_dir($dirPath)) {
            self::delete($dirPath);
        }
        self::create($dirPath, $mode, $recursive);

        return $dirPath;
    }

    /**
     * @param string $dirPath
     * @param int $mode
     * @param bool $recursive
     * @throws IoException
     * @return string Path of created directory.
     */
    public static function create($dirPath, $mode = 0755, $recursive = true) {
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

    public static function isEntry($path) {
        return is_file($path) || is_dir($path) || is_link($path);
    }

    private static function ensureDirExists($dirPath) {
        if (!is_dir($dirPath) || empty($dirPath)) {
            throw new IoException("The '$dirPath' directory does not exist.");
        }
    }
}
