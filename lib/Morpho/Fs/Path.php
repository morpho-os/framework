<?php
namespace Morpho\Fs;

use Morpho\Base\SecurityException;
use function Morpho\Base\unpackArgs;

class Path {
    const BASE64_URI_REGEXP = '[A-Za-z0-9+\\-_]';

    public static function isAbsolute(string $path): bool {
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
        if (false !== strpos($path, '\\') || false !== strpos($path, '..')) {
            return false;
        }
        return substr($path, -1, 1) !== '/';
    }

    public static function normalize(string $path): string {
        $path = str_replace('\\', '/', $path);
        if ($path === '/') {
            return $path;
        }
        return rtrim($path, '/');
    }

    public static function combine(...$paths): string {
        $paths = unpackArgs($paths);

        $result = [];
        $i = 0;
        foreach ($paths as $path) {
            $path = (string) $path;
            if ($path === '') {
                continue;
            }

            $path = str_replace('\\', '/', $path);

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

    public static function toAbsolute(string $path, bool $normalize = true): string {
        $absPath = realpath($path);
        if (false === $absPath) {
            throw new IoException("Unable to detect absolute path for the '$path' path.");
        }
        return $normalize ? self::normalize($absPath) : $absPath;
    }

    public static function toRelative(string $basePath, string $path): string {
        $path = static::normalize($path);
        $basePath = static::normalize($basePath);

        if ($path === '') {
            return $basePath;
        }
        if (empty($basePath)) {
            throw new IoException("The base path can't be empty.");
        }
        $pos = strpos($path, $basePath);
        if ($pos !== 0) {
            throw new IoException("The path '$path' does not contain the base path '$basePath'.");
        }

        return (string)substr($path, strlen($basePath) + 1);
    }

    public static function nameWithoutExt(string $path): string {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function nameWithNewExt(string $path, string $ext): string {
        return self::nameWithoutExt($path) . '.' . $ext;
    }

    /**
     * @see http://tools.ietf.org/html/rfc4648#section-5
     * @see http://php.net/base64_encode#103849
     */
    public static function base64Encode(string $uri): string {
        return rtrim(
            strtr(
                base64_encode($uri),
                '+/',
                '-_'
            ),
            '='
        );
    }

    public static function base64Decode(string $uri): string {
        return base64_decode(
            str_pad(
                strtr($uri, '-_', '+/'),
                strlen($uri) % 4,
                '=',
                STR_PAD_RIGHT
            )
        );
    }
}
