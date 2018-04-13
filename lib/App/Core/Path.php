<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Core;

use Morpho\Base\Environment;

abstract class Path {
    public static function rel(string $path, string $basePath): string {
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
            throw new \RuntimeException("The path '$path' does not contain the base path '$basePath'");
        }

        return (string)substr($path, strlen($basePath) + 1);
    }

    /**
     * This method taken from https://github.com/zendframework/zend-uri/blob/master/src/Uri.php and changed to match our requirements.
     *
     * @link      http://github.com/zendframework/zf2 for the canonical source repository
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * Remove any extra dot segments (/../, /./) from a path
     *
     * Algorithm is adapted from RFC-3986 section 5.2.4
     * (@link http://tools.ietf.org/html/rfc3986#section-5.2.4)
     *
     * @TODO   consider optimizing
     *
     * @param string|Path
     */
    public static function removeDotSegments(string $path): string {
        $output = '';

        while ($path) {
            if ($path == '..' || $path == '.') {
                break;
            }

            switch (true) {
                case ($path == '/.'):
                    $path = '/';
                    break;
                case ($path == '/..'):
                    $path   = '/';
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 4) == '/../'):
                    $path   = '/' . mb_substr($path, 4);
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 3) == '/./'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 2) == './'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 3) == '../'):
                    $path = mb_substr($path, 3);
                    break;
                default:
                    $slash = mb_strpos($path, '/', 1);
                    if ($slash === false) {
                        $seg = $path;
                    } else {
                        $seg = mb_substr($path, 0, $slash);
                    }

                    $output .= $seg;
                    $path = mb_substr($path, mb_strlen($seg));
                    break;
            }
        }

        return $output;
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
            $path = static::removeDotSegments($path);
        }
        return rtrim($path, '/\\');
    }
}
