<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Fs;

use Morpho\Base\ArrayTool;
use DirectoryIterator;
use Morpho\Base\Environment;
use Morpho\Error\ErrorHandler;
use InvalidArgumentException;

class Directory extends Entry {
    public const PHP_FILES_RE = '~\.php$~si';

    public static function move(string $sourceDirPath, string $targetDirPath): string {
        // @TODO: why not rename()?
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
                'type' => Stat::ENTRY,
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

    public static function copyContents($sourceDirPath, $targetDirPath): string {
        foreach (new \DirectoryIterator($sourceDirPath) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $entryPath = $item->getPathname();
            $relPath = Path::toRelative($sourceDirPath, $entryPath);
            Entry::copy($entryPath, $targetDirPath . '/' . $relPath);
        }
        return $targetDirPath;
    }

    /**
     * @param string|iterable $dirPaths
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
                'type'           => Stat::ENTRY,
            ]
        );

        if (is_string($processor)) {
            $regexp = $processor;
            $processor = function ($path, $isDir) use ($regexp) {
                return $isDir || preg_match($regexp, $path);
            };
        }
        if (is_string($dirPaths)) {
            $dirPaths = (array) $dirPaths;
        }
        $recursive = $options['recursive'];
        foreach ($dirPaths as $dirPath) {
            foreach (new DirectoryIterator($dirPath) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $path = Path::normalize($item->getPathname());
                $isDir = $item->isDir();

                if ($isDir) {
                    $match = $options['type'] & Stat::DIR;
                } else {
                    $match = $options['type'] & Stat::FILE;
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

    public static function baseNames($dirPath, $processor, array $options = null): \Generator {
        if (null !== $processor) {
            $processor = function ($path) use ($processor) {
                $baseName = basename($path);
                if (is_string($processor)) {
                    if (preg_match($processor, $baseName)) {
                        return $baseName;
                    }
                    return false;
                } elseif (!$processor instanceof \Closure) {
                    throw new Exception("Invalid processor");
                }
                $res = $processor($baseName, $path);
                if ($res === true) {
                    return $baseName;
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
     * Shortcut for the paths() with $options['type'] == Stat::DIR option.
     *
     * @param string|iterable $dirPath
     * @param string|\Closure $processor
     */
    public static function dirPaths($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = Stat::DIR;
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
        $options['type'] = Stat::DIR;
        return self::baseNames($dirPath, $processor, $options);
    }

    /**
     * Shortcut for the paths() with $options['type'] == Stat::FILE option.
     *
     * @param string|array $dirPath
     * @param string|\Closure $processor
     */
    public static function filePaths($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = Stat::FILE;
        return self::paths($dirPath, $processor, $options);
    }

    public static function filePathsWithExt($dirPath, array $extensions, array $options = []): \Generator {
        foreach ($extensions as $k => $extension) {
            $extensions[$k] = preg_quote($extension, '/');
        }
        return self::filePaths($dirPath, '/\.(' . implode('|', $extensions) . ')$/si', $options);
    }

    public static function fileNames($dirPath, $processor = null, array $options = []): \Generator {
        $options['type'] = Stat::FILE;
        return self::baseNames($dirPath, $processor, $options);
    }

    public static function linkPaths(string $dirPath, callable $filter): \Generator {
        foreach (Directory::paths($dirPath) as $path) {
            if (is_link($path)) {
                if ($filter) {
                    if ($filter($path)) {
                        yield $path;
                    }
                } else {
                    yield $path;
                }
            }
        }
    }

    public static function brokenLinkPaths($dirPath): \Generator {
        return Directory::linkPaths($dirPath, [Symlink::class, 'isBroken']);
    }

    /**
     * @return Path to the created directory.
     */
    public static function createTmp(string $relativeDirPath, int $mode = Stat::DIR_MODE): string {
        return self::create(
            Path::combine(Environment::tmpDirPath(), $relativeDirPath),
            $mode
        );
    }

    /**
     * Deletes files and directories recursively from a file system.
     *
     * This method recursively removes the $dirPath and all its contents. You should be extremely careful with this method as it has the potential to erase everything that the current user has access to.
     *
     * @param string|iterable $dirPath
     * @param bool|callable $predicateFnOrFlag If callable then it must return true for the all entries which will be deleted and false otherwise. If boolean it must return true if the directory $dirPath must be deleted and false otherwise.
     */
    public static function delete($dirPath, $predicateFnOrFlag = true): void {
        if (is_iterable($dirPath)) {
            foreach ($dirPath as $path) {
                static::delete_($path, $predicateFnOrFlag);
            }
        } else {
            static::delete_($dirPath, $predicateFnOrFlag);
        }
    }

    /**
     * @param string|iterable $dirPath
     * @param bool|callable $predicate
     */
    public static function deleteIfExists($dirPath, $predicate = true): void {
        if (is_iterable($dirPath)) {
            foreach ($dirPath as $path) {
                if (is_dir($path)) {
                    self::delete_($path, $predicate);
                }
            }
        } else {
            if (is_dir($dirPath)) {
                self::delete_($dirPath, $predicate);
            }
        }
    }

    public static function emptyDirPaths($dirPath, callable $predicate = null): iterable {
        foreach ((array)$dirPath as $dPath) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $fileInfo) {
                $path = $fileInfo->getPathname();
                if (is_dir($path) && self::isEmpty($path)) {
                    if ($predicate && !$predicate($path)) {
                        continue;
                    }
                    yield $path;
                }
            }
        }
    }

    public static function deleteEmptyDirs($dirPath, callable $predicate = null): void {
        foreach (self::emptyDirPaths($dirPath, $predicate) as $dPath) {
            self::delete($dPath);
        }
    }

    public static function isEmpty($dirPath): bool {
        foreach (self::paths($dirPath, null, ['recursive' => false]) as $_) {
            return false;
        }
        return true;
    }

    public static function recreate(string $dirPath, int $mode = Stat::DIR_MODE, bool $recursive = true): string {
        if (is_dir($dirPath)) {
            self::delete($dirPath);
        }
        self::create($dirPath, $mode, $recursive);

        return $dirPath;
    }

    public static function create(string $dirPath, int $mode = Stat::DIR_MODE, bool $recursive = true): string {
        if ('' === $dirPath) {
            throw new Exception("The directory path is empty");
        }

        if (is_dir($dirPath)) {
            return $dirPath;
        }

        ErrorHandler::checkError(@mkdir($dirPath, $mode, $recursive), "Unable to create the directory '$dirPath' with mode: $mode");

        return $dirPath;
    }

    public static function mustExist(string $dirPath): string {
        if ('' === $dirPath) {
            throw new Exception("The directory path is empty");
        }
        if (!is_dir($dirPath)) {
            throw new Exception("The '$dirPath' directory does not exist");
        }
        return $dirPath;
    }

    /**
     * @return mixed
     */
    public static function usingAnother(string $otherDirPath, callable $fn) {
        $curDirPath = getcwd();
        try {
            chdir($otherDirPath);
            $res = $fn($otherDirPath);
        } finally {
            chdir($curDirPath);
        }
        return $res;
    }

    private static function delete_(string $dirPath, $predicateOrDeleteSelf) {
        if (is_callable($predicateOrDeleteSelf)) {
            self::delete__($dirPath, $predicateOrDeleteSelf);
        } elseif (is_bool($predicateOrDeleteSelf)) {
            if ($predicateOrDeleteSelf) {
                // Delete self
                $predicate = null;
            } else {
                // Not delete self
                $predicate = function ($path, $isDir) use ($dirPath) {
                    return $path !== $dirPath;
                };
            }
            self::delete__($dirPath, $predicate);
        } else {
            throw new InvalidArgumentException('The second argument must be either bool or callable');
        }
    }

    /**
     * This method uses code which was found in eZ Components (ezcBaseFile::removeRecursive() method).
     * @param callable|null $predicate Predicate selects entries which will be deleted.
     */
    private static function delete__(string $dirPath, ?callable $predicate): void {
        self::mustExist($dirPath);
        $absPath = realpath($dirPath);
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $absPath = str_replace('\\', '/', $absPath);
        }
        if (!$absPath) {
            throw new Exception("The directory '$dirPath' could not be found");
        }
        $dir = @dir($absPath);
        if (!$dir) {
            throw new Exception("The directory '$dirPath' can not be opened for reading");
        }

        $parentDirPath = realpath($dirPath . '/..');
        if (!is_writable($parentDirPath)) {
            if (null !== $predicate) {
                // This directory must be deleted if $predicate returns true
                if ($predicate($dirPath, true)) {
                    throw new Exception("The directory '$dirPath' can not be opened for writing");
                }
            } else {
                throw new Exception("The directory '$dirPath' can not be opened for writing");
            }
        }

        while (false !== ($entryName = $dir->read())) {
            if ($entryName == '.' || $entryName == '..') {
                continue;
            }
            $entryPath = $absPath . '/' . $entryName;

            $isDir = is_dir($entryPath);
            if ($isDir) {
                if (null !== $predicate) {
                    if ($predicate($entryPath, true)) {
                        // If it is a directory and we need to delete this directory, delete contents regardless of the $predicate, so pass the `null` as the second argument.
                        self::delete__($entryPath, null);
                    } else {
                        // The $predicate can be used for the directory contents, so pass it as the argument.
                        self::delete__($entryPath, $predicate);
                    }
                } else {
                    self::delete__($entryPath, null);
                }
            } else {
                if (null === $predicate || (null !== $predicate && $predicate($entryPath, false))) {
                    ErrorHandler::checkError(@unlink($entryPath), "The file '$entryPath' can not be deleted, check permissions");
                }
            }
        }

        $dir->close();
        if (null === $predicate || (null !== $predicate && $predicate($absPath, true))) {
            ErrorHandler::checkError(@rmdir($absPath), "Unable to delete the directory '$absPath': it may be not empty or doesn't have relevant permissions");
        }
    }
}
