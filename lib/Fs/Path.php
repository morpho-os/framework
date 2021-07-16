<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use Morpho\Base\Path as BasePath;
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
use function pathinfo;
use function strlen;
use function strpos;
use function substr;

class Path extends BasePath {
    public static function isAbs(string $path): bool {
        return $path !== '' && $path[0] === '/' || self::isAbsWinPath($path);
    }

    public static function isAbsWinPath(string $path): bool {
        return (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === '/' || $path[2] === '\\'));
    }

    public static function assertSafe(string $path): string {
        if (str_contains($path, "\x00") || str_contains($path, '..')) {
            throw new SecurityException("Invalid file path was detected.");
        }
        return $path;
    }

    public static function normalize(string $path, bool $removeDotSegments = true): string {
        if (self::isAbsWinPath($path)) {
            return str_replace('\\', '/', substr($path, 0, 3)) . parent::normalize(substr($path, 3));
        }
        return parent::normalize($path, $removeDotSegments);
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
     * NB: This is not safe if multiple threads (processes) can work with the same $path.
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
