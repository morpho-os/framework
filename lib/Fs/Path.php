<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use Morpho\App\Path as BasePath;
use Morpho\Base\Env;
use Morpho\Base\NotImplementedException;
use Morpho\Base\SecurityException;

use function array_pop;
use function count;
use function dirname;
use function explode;
use function file_exists;
use function implode;
use function is_file;
use function ltrim;
use function Morpho\Base\unpackArgs;
use function pathinfo;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

class Path extends BasePath {
    public static function isAbs(string $path): bool {
        return $path !== ''
            && $path[0] === '/'
            || (isset($path[1]) && $path[1] === ':'); // preg_match('~^[a-zA-Z]+:~', $path);
    }

    public static function assertSafe(string $path) {
        if (false !== strpos($path, "\x00") || false !== strpos($path, '..')) {
            throw new SecurityException("Invalid file path was detected.");
        }
    }

    public static function isNormalized(string $path): bool {
        $isWindows = Env::isWindows();
        if ($isWindows) {
            if (false !== strpos($path, '\\')) {
                return false;
            }
        }
        $last = substr($path, -1, 1);
        return $last !== '/'
            && (!$isWindows && $last !== '\\')
            && false === strpos($path, '..');
    }

    public static function combine(...$paths): string {
        $paths = unpackArgs($paths);

        $result = [];
        $i = 0;
        $isWindows = Env::isWindows();
        foreach ($paths as $path) {
            $path = (string) $path;
            if ($path === '') {
                continue;
            }

            if ($isWindows) {
                $path = str_replace('\\', '/', $path);
            }

            if (!$i) {
                if ($path[0] === '/') {
                    $result[] = '';
                }
            }
            $i++;
            $path = trim($path, '/');
            if (empty($path)) {
                continue;
            }
            $result[] = $path;
        }

        return (count($result) === 1 && $result[0] === '')
            ? '/'
            : implode('/', $result);
    }

    public static function abs(string $path, bool $normalize = true): string {
        $absPath = self::removeDotSegments($path);
        return $normalize ? self::normalize($absPath) : $absPath;
    }

    public static function ext(string $path): string {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function fileName(string $path): string {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    public static function dirPath(string $path): string {
        // Handle paths like vfs:///foo/bar
        $pos = strpos($path, ':');
        if (false !== $pos && preg_match('~^([a-z]+://)(.*)$~si', $path, $match)) {
            return $match[1] . dirname($match[2]);
        }
        return dirname($path);
    }

    public static function dropExt(string $path): string {
        return self::changeExt($path, '');
    }

    public static function changeExt(string $path, string $newExt): string {
        $parts = explode('/', self::normalize($path));
        $fileName = array_pop($parts);
        if (!empty($newExt)) {
            $newExt = '.' . self::normalizeExt($newExt);
            $extLength = strlen($newExt);
            if (substr($path, -$extLength) === $newExt) {
                $baseName = substr($fileName, 0, -$extLength);
            } else {
                $baseName = self::nameWithoutExt($fileName);
            }
        } else {
            $baseName = self::nameWithoutExt($fileName);
        }
        return count($parts)
            ? implode('/', $parts) . '/' . $baseName . $newExt
            : $baseName . $newExt;
    }

    public static function normalizeExt(string $ext): string {
        return ltrim($ext, '.');
    }

    public static function nameWithoutExt(string $path): string {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Returns unique path for a file system entry.
     */
    public static function unique(
        string $path,
        ?bool $handleExtsForFiles = true,
        int $numberOfAttempts = 10000
    ): string {
        Dir::mustExist(dirname($path));
        $uniquePath = $path;
        $isFile = is_file($path);
        for ($i = 0; file_exists($uniquePath) && $i < $numberOfAttempts; $i++) {
            if ($isFile && $handleExtsForFiles) {
                $pathInfo = pathinfo($path);
                $uniquePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $i . (isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '');
            } else {
                $uniquePath = $path . '-' . $i;
            }
        }
        if ($i == $numberOfAttempts && file_exists($uniquePath)) {
            throw new Exception("Unable to generate an unique path for the '$path' (tried $i times)");
        }
        return $uniquePath;
    }

    public static function parentPaths(string $path): array {
        throw new NotImplementedException();
    }
}
