<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use Morpho\Base\Environment;
use Morpho\Base\SecurityException;
use function Morpho\Base\unpackArgs;
use Morpho\Web\Uri\Path as UriPath;

class Path {
    public static function isAbs(string $path): bool {
        return $path !== ''
            && ($path[0] === '/' || $path[0] === '\\')
            || (isset($path[1]) && $path[1] === ':');
    }

    public static function assertSafe(string $path) {
        if (false !== strpos($path, "\x00") || false !== strpos($path, '..')) {
            throw new SecurityException("Invalid file path was detected.");
        }
    }

    public static function isNormalized(string $path): bool {
        $isWindows = Environment::isWindows();
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

    public static function normalize(string $path): string {
        if ($path === '') {
            return $path;
        }
        if (Environment::isWindows()) {
            $path = str_replace('\\', '/', $path);
        }
        if ($path === '/') {
            return $path;
        }
        if (false !== strpos($path, '/..')) {
            $path = UriPath::removeDotSegments($path);
        }
        return rtrim($path, '/\\');
    }

    public static function combine(...$paths): string {
        $paths = unpackArgs($paths);

        $result = [];
        $i = 0;
        $isWindows = Environment::isWindows();
        foreach ($paths as $path) {
            $path = (string)$path;
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

    public static function toAbs(string $path, bool $normalize = true): string {
        $absPath = realpath($path);
        if (false === $absPath) {
            throw new Exception("Unable to detect absolute path for the '$path' path.");
        }
        return $normalize ? self::normalize($absPath) : $absPath;
    }

    public static function toRel(string $path, string $basePath): string {
        $path = static::normalize($path);
        $basePath = static::normalize($basePath);

        if ($path === '') {
            return $basePath;
        }
        if ($basePath === '') {
            return $path;
        }
        $pos = strpos($path, $basePath);
        if ($pos !== 0) {
            throw new Exception("The path '$path' does not contain the base path '$basePath'");
        }

        return (string)substr($path, strlen($basePath) + 1);
    }

    public static function ext(string $path): string {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function nameWithoutExt(string $path): string {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function fileName(string $path): string {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    public static function normalizeExt(string $ext): string {
        return ltrim($ext, '.');
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

    /**
     * Returns unique path for a file system entry.
     */
    public static function unique(string $path, ?bool $handleExtsForFiles = true, int $numberOfAttempts = 10000): string {
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
}
