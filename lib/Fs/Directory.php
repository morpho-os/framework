<?php
declare(strict_types = 1);

namespace Morpho\Fs;

use Morpho\Base\NotImplementedException;
use Morpho\Base\ArrayTool;
use DirectoryIterator;
use Morpho\Error\ErrorHandler;

class Directory extends Entry {
    public const FILE = 0x01;
    public const DIR = 0x02;

    public const MODE = 0755;

    public const PHP_FILES_RE = '~.\.php$~si';

    public static function move(string $sourceDirPath, string $targetDirPath): string {
        self::copy($sourceDirPath, $targetDirPath);
        self::delete($sourceDirPath);
        return $targetDirPath;
    }

    public static function copy(string $sourceDirPath, string $targetDirPath, $processor = null, array $options = null): string {
        // @TODO: Handle dots and relative paths: '..', '.'
        // @TODO: Handle the case: cp module/system ../../dst/module should create ../../dst/module/system
        self::mustExist($sourceDirPath);

        if ($sourceDirPath === $targetDirPath) {
            throw new Exception("Cannot copy the directory '$sourceDirPath' into itself");
        }

        $options = ArrayTool::handleOptions(
            (array) $options,
            [
                'overwrite'      => false,
                'followSymlinks' => false,
                'skipIfExists'   => false,
            ]
        );

        if (is_dir($targetDirPath)) {
            $sourceDirName = basename($sourceDirPath);
            if ($sourceDirName !== basename($targetDirPath)) {
                $targetDirPath .= '/' . $sourceDirName;
            }
            if ($sourceDirPath === $targetDirPath) {
                throw new Exception("The '" . dirname($targetDirPath) . "' directory already contains the '$sourceDirName'");
            }
        }

        $targetDirPath = self::create($targetDirPath, fileperms($sourceDirPath));

        $paths = self::paths(
            $sourceDirPath,
            $processor,
            [
                'recursive' => false,
                'type' => self::FILE | self::DIR,
                'followSymlinks' => $options['followSymlinks'],
            ]
        );
        foreach ($paths as $path) {
            $targetPath = $targetDirPath . '/' . basename($path);
            if (is_file($path) || is_link($path)) {
                File::copy($path, $targetPath, $options['overwrite'], $options['skipIfExists']);
            } else {
                self::copy($path, $targetPath, $processor, $options);
            }
        }

        return $targetDirPath;
    }

    /**
     * @param array|string $dirPaths
     * @param string|\Closure $processor
     */
    public static function paths($dirPaths, $processor = null, array $options = []): \Generator {
        if (null !== $processor && !is_string($processor) && !$processor instanceof \Closure) {
            throw new Exception("Invalid processor");
        }
        $options = ArrayTool::handleOptions(
            $options,
            [
                'recursive'      => false,
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

        $recursive = $options['recursive'];
        foreach ((array)$dirPaths as $dirPath) {
            foreach (new DirectoryIterator($dirPath) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $path = str_replace('\\', '/', $item->getPathname());
                $isDir = $item->isDir();

                if ($isDir) {
                    $match = $options['type'] & self::DIR;
                } else {
                    $match = $options['type'] & self::FILE;
                }
                if (!$match) {
                    if (!$isDir || !$recursive) {
                        continue;
                    }
                } else {
                    if (null !== $processor) {
                        $modifiedPath = $processor($path, $isDir);
                        if (false === $modifiedPath) {
                            continue;
                        } elseif (true !== $modifiedPath && null !== $modifiedPath) {
                            $path = $modifiedPath;
                        }
                    }
                    yield $path;
                }

                if ($isDir && $recursive) {
                    if ($item->isLink() && !$options['followSymlinks']) {
                        continue;
                    }

                    yield from self::paths($item->getPathname(), $processor, $options);
                }
            }
        }
    }

    /**
     * Shortcut for the paths() with $options['type'] == self::DIR option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function dirPaths($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = self::DIR;
        if (null !== $processor) {
            $processor = function ($path) use ($processor) {
                if (is_string($processor)) {
                    return (bool) preg_match($processor, $path);
                } elseif (!$processor instanceof \Closure) {
                    throw new Exception("Invalid processor");
                }
                return $processor($path, true);
            };
        }
        return self::paths($dirPath, $processor, $options);
    }

    public static function dirNames($dirPath, $processor = null, array $options = null): \Generator {
        if (!empty($options['recursive'])) {
            throw new \LogicException("The 'recursive' option must be false");
        }
        $options['type'] = self::DIR;
        if (null !== $processor) {
            $processor = function ($path) use ($processor) {
                $dirName = basename($path);
                if (is_string($processor)) {
                    if (preg_match($processor, $dirName)) {
                        return $dirName;
                    }
                    return false;
                } elseif (!$processor instanceof \Closure) {
                    throw new Exception("Invalid processor");
                }
                $res = $processor($dirName, $path);
                if ($res === true) {
                    return $dirName;
                }
                return $res;
            };
        } else {
            $processor = function ($path) {
                return basename($path);
            };
        }
        return self::paths($dirPath, $processor, $options);
    }

    /**
     * Shortcut for the paths() with $options['type'] == self::FILE option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function filePaths($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = self::FILE;
        return self::paths($dirPath, $processor, $options);
    }

    public static function filePathsWithExt($dirPath, array $extensions, array $options = []): \Generator {
        foreach ($extensions as $k => $extension) {
            $extensions[$k] = preg_quote($extension, '/');
        }
        return self::filePaths($dirPath, '/\.(' . implode('|', $extensions) . ')$/si', $options);
    }

    public static function linkPaths(string $dirPath, $processor = null): \Generator {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * @TODO: Extract linkPaths() method, use it with $processor that will check link and
     * return true for broken links. Add $options
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function brokenLinkPaths($dirPath, $processor = null): \Generator {
        foreach (Directory::paths($dirPath, $processor) as $linkOrOtherEntryPath) {
            if (is_link($linkOrOtherEntryPath)) {
                $targetPath = readlink($linkOrOtherEntryPath);
                // @TODO: Handle relative paths, see Symlink::isBroken()
                if (false === $targetPath || !self::isEntry($targetPath)) {
                    yield $linkOrOtherEntryPath => $targetPath;
                }
            }
        }
    }

    public static function tmpPath(): string {
        return Path::normalize(sys_get_temp_dir());
    }

    /**
     * @return Path to the created directory.
     */
    public static function createTmp(string $relativeDirPath, int $mode = self::MODE): string {
        return self::create(
            Path::combine(self::tmpPath(), $relativeDirPath),
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
     *
     * @param string|iterable $dirPath
     * @param bool|callable $deleteSelfOrPredicate
     */
    public static function delete($dirPath, $deleteSelfOrPredicate = true): void {
        if (is_iterable($dirPath)) {
            foreach ($dirPath as $path) {
                self::deleteDir($path, $deleteSelfOrPredicate);
            }
        } else {
            self::deleteDir($dirPath, $deleteSelfOrPredicate);
        }
    }

    /**
     * @param string|iterable $dirPath
     * @param bool|callable $deleteSelfOrPredicate
     */
    public static function deleteIfExists($dirPath, $deleteSelfOrPredicate = true): void {
        if (is_iterable($dirPath)) {
            foreach ($dirPath as $path) {
                if (is_dir($path)) {
                    self::deleteDir($path, $deleteSelfOrPredicate);
                }
            }
        } else {
            if (is_dir($dirPath)) {
                self::deleteDir($dirPath, $deleteSelfOrPredicate);
            }
        }
    }

    public static function deleteEmptyDirs(string $dirPath, callable $predicate = null): void {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $fileInfo) {
            $path = $fileInfo->getPathname();
            if (is_dir($path) && self::isEmpty($path)) {
                if ($predicate && !$predicate($path)) {
                    continue;
                }
                Directory::delete($path);
            }
        }
    }

    public static function isEmpty($dirPath): bool {
        foreach (self::paths($dirPath, null, ['recursive' => false]) as $path) {
            return false;
        }
        return true;
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
            throw new Exception("Unable to generate an unique path for the directory '$dirPath' (tried $i times)");
        }

        return $uniquePath;
    }

    public static function recreate(string $dirPath, int $mode = self::MODE, bool $recursive = true): string {
        if (is_dir($dirPath)) {
            self::delete($dirPath);
        }
        self::create($dirPath, $mode, $recursive);

        return $dirPath;
    }

    public static function create(string $dirPath, int $mode = self::MODE, bool $recursive = true): string {
        if (empty($dirPath)) {
            throw new Exception("The directory path is empty.");
        }

        if (is_dir($dirPath)) {
            return $dirPath;
        }

        ErrorHandler::checkError(@mkdir($dirPath, $mode, $recursive), "Unable to create the directory '$dirPath' with mode: $mode");

        return $dirPath;
    }

    public static function mustExist(string $dirPath): string {
        if (empty($dirPath)) {
            throw new Exception("The directory path is empty");
        }
        if (!is_dir($dirPath)) {
            throw new Exception("The '$dirPath' directory does not exist");
        }
        return $dirPath;
    }

    /**
     * @param string $dirPath
     * @param bool|callable $deleteSelfOrPredicate Predicate selects entries which will be not deleted.
     */
    private static function deleteDir(string $dirPath, $deleteSelfOrPredicate = null): void {
        static::mustExist($dirPath);
        $isBoolArg = is_bool($deleteSelfOrPredicate);
        if (!$isBoolArg && !is_callable($deleteSelfOrPredicate)) {
            throw new \InvalidArgumentException('The second argument must be bool or callable');
        }
        $absFilePath = realpath($dirPath);
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $absFilePath = str_replace('\\', '/', $absFilePath);
        }
        if (!$absFilePath) {
            throw new Exception("The directory '$dirPath' could not be found");
        }
        $d = @dir($absFilePath);
        if (!$d) {
            throw new Exception("The directory '$dirPath' can not be opened for reading");
        }
        // Check if we can delete the dir.
        if (!is_writable(realpath($dirPath . '/' . '..'))) {
            throw new Exception("The directory '$dirPath' can not be opened for writing");
        }
        // Loop over contents.
        while (($fileName = $d->read()) !== false) {
            if ($fileName == '.' || $fileName == '..') {
                continue;
            }
            $filePath = $absFilePath . '/' . $fileName;
            if (is_dir($filePath)) {
                static::deleteDir($filePath, $deleteSelfOrPredicate);
            } else {
                if (!$isBoolArg && $deleteSelfOrPredicate($filePath)) {
                    continue;
                }
                ErrorHandler::checkError(@unlink($filePath), "The file '$filePath' can not be deleted, check permissions");
            }
        }
        $d->close();
        if (!$isBoolArg) {
            $skip = $deleteSelfOrPredicate($absFilePath);
        } else {
            $skip = !$deleteSelfOrPredicate;
        }
        if (!$skip) {
            ErrorHandler::checkError(@rmdir($absFilePath), "Unable to delete the directory '$absFilePath': it may be not empty or doesn't have relevant permissions");
        }
    }
}
