<?php
namespace Morpho\Fs;

use Morpho\Base\SecurityException;

class Path {
    const BASE64_URI_REGEXP = '[A-Za-z0-9+\\-_]';

    public static function isAbsolute($path) {
        return $path !== ''
        && ($path[0] === '/' || $path[0] === '\\')
        || (isset($path[1]) && $path[1] === ':');
    }

    public static function findRootDirPath($dirPath = null, $throwEx = true) {
        if (null === $dirPath) {
            $dirPath = __DIR__;
        }
        $rootDirPath = null;
        do {
            $path = $dirPath . '/vendor/composer/ClassLoader.php';
            if (is_file($path)) {
                $rootDirPath = $dirPath;
                break;
            } else {
                $chunks = explode(DIRECTORY_SEPARATOR, $dirPath, -1);
                $dirPath = implode(DIRECTORY_SEPARATOR, $chunks);
            }
        } while ($chunks);
        if (null === $rootDirPath) {
            if ($throwEx) {
                throw new \RuntimeException("Unable to find path of root directory.");
            }
            return null;
        }
        return str_replace('\\', '/', $rootDirPath);
    }

    public static function assertSafe($filePath) {
        if (false !== strpos($filePath, "\x00") || false !== strpos($filePath, '..')) {
            throw new SecurityException("Invalid file path was detected.");
        }
    }

    public static function isNormalized($path) {
        if (false !== strpos($path, '\\') || false !== strpos($path, '..')) {
            return false;
        }
        return substr($path, -1, 1) !== '/';
    }

    public static function normalize($path) {
        $path = str_replace('\\', '/', $path);
        if ($path === '/') {
            return $path;
        }
        return rtrim($path, '/');
    }

    public static function combine(...$paths) {
        $paths = unpackArgs($paths);

        $result = [];
        $i = 0;
        foreach ($paths as $path) {
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

    /**
     * @param $path string
     * @param bool $normalize
     * @return string
     */
    public static function toAbsolute($path, $normalize = true) {
        $absPath = realpath($path);
        if (false === $absPath) {
            throw new IoException("Unable to detect absolute path for the '$path' path.");
        }
        return $normalize ? self::normalize($absPath) : $absPath;
    }

    /**
     * @param $basePath string
     * @param $path string
     * @return string
     */
    public static function toRelative($basePath, $path) {
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

    public static function nameWithoutExt($path) {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function nameWithNewExt($path, $ext) {
        return pathinfo($path, PATHINFO_FILENAME) . '.' . $ext;
    }

    /**
     * @see http://tools.ietf.org/html/rfc4648#section-5
     * @see http://php.net/base64_encode#103849
     * @param string $uri
     * @return string
     */
    public static function base64Encode($uri) {
        return rtrim(
            strtr(
                base64_encode($uri),
                '+/',
                '-_'
            ),
            '='
        );
    }

    /**
     * @param string $uri
     * @return string
     */
    public static function base64Decode($uri) {
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
