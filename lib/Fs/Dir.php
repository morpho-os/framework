<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use DirectoryIterator;
use Morpho\Base\Config;
use Morpho\Base\Environment;
use Morpho\Error\ErrorHandler;
use InvalidArgumentException;

class Dir extends Entry {
    public const PHP_FILE_RE = '~\.php$~si';

    public static function move(string $sourceDirPath, string $targetDirPath): string {
        // @TODO: why not rename()?
        self::copy($sourceDirPath, $targetDirPath);
        self::delete($sourceDirPath);
        return $targetDirPath;
    }

    public static function copy(string $sourceDirPath, string $targetDirPath, $processor = null, array $config = null): string {
        // @TODO: Handle dots and relative paths: '..', '.'
        // @TODO: Handle the case: cp module/system ../../dst/module should create ../../dst/module/system
        self::mustExist($sourceDirPath);

        if ($sourceDirPath === $targetDirPath) {
            throw new Exception("Cannot copy the directory '$sourceDirPath' into itself");
        }

        $config = Config::check(
            [
                'overwrite'      => false,
                'followLinks' => false,
                'skipIfExists'   => false,
            ],
            (array) $config
        );

        if (\is_dir($targetDirPath)) {
            $sourceDirName = \basename($sourceDirPath);
            if ($sourceDirName !== \basename($targetDirPath)) {
                $targetDirPath .= '/' . $sourceDirName;
            }
            if ($sourceDirPath === $targetDirPath) {
                throw new Exception("The '" . \dirname($targetDirPath) . "' directory already contains the '$sourceDirName'");
            }
        }

        $targetDirPath = self::create($targetDirPath, \fileperms($sourceDirPath));

        $paths = self::paths(
            $sourceDirPath,
            $processor,
            [
                'recursive' => false,
                'type' => Stat::ENTRY,
                'followLinks' => $config['followLinks'],
            ]
        );
        foreach ($paths as $path) {
            $targetPath = $targetDirPath . '/' . \basename($path);
            if (\is_file($path) || \is_link($path)) {
                File::copy($path, $targetPath, $config['overwrite'], $config['skipIfExists']);
            } else {
                self::copy($path, $targetPath, $processor, $config);
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
            $relPath = Path::rel($entryPath, $sourceDirPath);
            Entry::copy($entryPath, $targetDirPath . '/' . $relPath);
        }
        return $targetDirPath;
    }

    /**
     * @param string|iterable $dirPaths
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function paths($dirPaths, $processor = null, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        if (null !== $processor && !\is_string($processor) && !$processor instanceof \Closure) {
            throw new Exception("Invalid processor");
        }
        $config = Config::check(
            [
                'recursive'      => false,
                'followLinks' => false,
                'type'           => Stat::ENTRY,
            ],
            $config
        );

        if (\is_string($processor)) {
            $regexp = $processor;
            $processor = function ($path, $isDir) use ($regexp) {
                return $isDir || \preg_match($regexp, $path);
            };
        }
        if (\is_string($dirPaths)) {
            $dirPaths = (array) $dirPaths;
        }
        $recursive = $config['recursive'];
        foreach ($dirPaths as $dirPath) {
            foreach (new DirectoryIterator($dirPath) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $path = Path::normalize($item->getPathname());
                $isDir = $item->isDir();

                if ($isDir) {
                    $match = $config['type'] & Stat::DIR;
                } else {
                    $match = $config['type'] & Stat::FILE;
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
                    if ($item->isLink() && !$config['followLinks']) {
                        continue;
                    }

                    yield from self::paths($item->getPathname(), $processor, $config);
                }
            }
        }
    }

    /**
     * @param string|iterable $dirPath
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function baseNames($dirPath, $processor, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        if (null !== $processor) {
            $processor = function ($path) use ($processor) {
                $baseName = \basename($path);
                if (\is_string($processor)) {
                    if (\preg_match($processor, $baseName)) {
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
                return \basename($path);
            };
        }
        return self::paths($dirPath, $processor, $config);
    }

    /**
     * Shortcut for the paths() with $config['type'] == Stat::DIR option.
     *
     * @param string|iterable $dirPath
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function dirPaths($dirPath, $processor = null, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        $config['type'] = Stat::DIR;
        if (null !== $processor) {
            $processor = function ($path) use ($processor) {
                if (\is_string($processor)) {
                    return (bool) \preg_match($processor, $path);
                } elseif (!$processor instanceof \Closure) {
                    throw new Exception("Invalid processor");
                }
                return $processor($path, true);
            };
        }
        return self::paths($dirPath, $processor, $config);
    }

    /**
     * @param iterable|string $dirPath
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function dirNames($dirPath, $processor = null, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        if (!empty($config['recursive'])) {
            throw new \LogicException("The 'recursive' config param must be false");
        }
        $config['type'] = Stat::DIR;
        return self::baseNames($dirPath, $processor, $config);
    }

    /**
     * Shortcut for the paths() with $config['type'] == Stat::FILE option.
     *
     * @param iterable|string $dirPath
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function filePaths($dirPath, $processor = null, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        $config['type'] = Stat::FILE;
        return self::paths($dirPath, $processor, $config);
    }

    /**
     * @param iterable|string $dirPath
     * @param array|bool|null $config
     */
    public static function filePathsWithExt($dirPath, array $extensions, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        foreach ($extensions as $k => $extension) {
            $extensions[$k] = \preg_quote($extension, '/');
        }
        return self::filePaths($dirPath, '/\.(' . \implode('|', $extensions) . ')$/si', $config);
    }

    /**
     * @param iterable|string $dirPath
     * @param string|\Closure $processor
     * @param array|bool|null $config
     */
    public static function fileNames($dirPath, $processor = null, $config = null): \Generator {
        $config = self::normalizeConfig($config);
        $config['type'] = Stat::FILE;
        return self::baseNames($dirPath, $processor, $config);
    }

    public static function linkPaths(string $dirPath, callable $filter): \Generator {
        foreach (Dir::paths($dirPath) as $path) {
            if (\is_link($path)) {
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
        return Dir::linkPaths($dirPath, [Link::class, 'isBroken']);
    }

    /**
     * @return Path to the created directory.
     */
    public static function createTmp(string $relDirPath, int $mode = Stat::DIR_MODE): string {
        return self::create(
            Path::combine(Environment::tmpDirPath(), $relDirPath),
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
        if (\is_iterable($dirPath)) {
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
        if (\is_iterable($dirPath)) {
            foreach ($dirPath as $path) {
                if (\is_dir($path)) {
                    self::delete_($path, $predicate);
                }
            }
        } else {
            if (\is_dir($dirPath)) {
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
                if (\is_dir($path) && self::isEmpty($path)) {
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

    /**
     * @param string|array $dirPath
     *     @TODO: Accept iterable
     * @return string|array string if $dirPath is a string, an array if the $dirPath is an array
     */
    public static function recreate($dirPath, int $mode = Stat::DIR_MODE, bool $recursive = true) {
        if (\is_array($dirPath)) {
            $res = [];
            foreach ($dirPath as $key => $path) {
                $res[$key] = self::recreate($path, $mode, $recursive);
            }
            return $res;
        } elseif (!\is_string($dirPath)) {
            throw new Exception('Invalid type of the argument');
        }
        if (\is_dir($dirPath)) {
            self::delete($dirPath);
        }
        self::create($dirPath, $mode, $recursive);

        return $dirPath;
    }

    /**
     * @param string|array $dirPath
     *     @TODO: Accept iterable
     * @return string|array string if $dirPath is a string, an array if the $dirPath is an array
     */
    public static function create($dirPath, ?int $mode = Stat::DIR_MODE, bool $recursive = true) {
        if (null === $mode) {
            $mode = Stat::DIR_MODE;
        }
        if (\is_array($dirPath)) {
            $res = [];
            foreach ($dirPath as $key => $path) {
                $res[$key] = self::create($path, $mode, $recursive);
            }
            return $res;
        } elseif (!\is_string($dirPath)) {
            throw new Exception('Invalid type of the argument');
        }

        if ('' === $dirPath) {
            throw new Exception("The directory path is empty");
        }

        if (\is_dir($dirPath)) {
            return $dirPath;
        }

        ErrorHandler::checkError(@\mkdir($dirPath, $mode, $recursive), "Unable to create the directory '$dirPath' with mode: $mode");

        return $dirPath;
    }

    public static function mustExist(string $dirPath): string {
        if ('' === $dirPath) {
            throw new Exception("The directory path is empty");
        }
        if (!\is_dir($dirPath)) {
            throw new Exception("The '$dirPath' directory does not exist");
        }
        return $dirPath;
    }

    /**
     * @return mixed
     */
    public static function doIn(string $otherDirPath, callable $fn) {
        $curDirPath = \getcwd();
        try {
            \chdir($otherDirPath);
            $res = $fn($otherDirPath);
        } finally {
            \chdir($curDirPath);
        }
        return $res;
    }

    private static function delete_(string $dirPath, $predicateOrDeleteSelf) {
        if (\is_callable($predicateOrDeleteSelf)) {
            self::delete__($dirPath, $predicateOrDeleteSelf);
        } elseif (\is_bool($predicateOrDeleteSelf)) {
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
     * @param callable|null $predicate Predicate selects entries which will be deleted.
     */
    private static function delete__(string $dirPath, ?callable $predicate): void {
        self::mustExist($dirPath);
        $absPath = Path::abs($dirPath, true);
        $it = new \DirectoryIterator($absPath);
        foreach ($it as $entry) {
            if ($entry->isDot()) {
                continue;
            }
            $entryPath = $entry->getPathname();
            if ($entry->isDir()) {
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
                    ErrorHandler::checkError(@\unlink($entryPath), "The file '$entryPath' can not be deleted, check permissions");
                }
            }
        }
        if (null === $predicate || (null !== $predicate && $predicate($absPath, true))) {
            ErrorHandler::checkError(@\rmdir($absPath), "Unable to delete the directory '$absPath': it may be not empty or doesn't have relevant permissions");
        }
    }

    /**
     * @param null|array|bool $config
     * @return array
     */
    private static function normalizeConfig($config): array {
        if (!\is_array($config)) {
            if (null === $config) {
                $config = [];
            } elseif (\is_bool($config)) {
                $config = ['recursive' => $config];
            } else {
                throw new \InvalidArgumentException();
            }
        }
        return $config;
    }
}
