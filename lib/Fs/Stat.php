<?php
declare(strict_types=1);

namespace Morpho\Fs;

class Stat {
    public static function modeString(string $path): string {
        return sprintf('%o', self::mode($path));
    }

    public static function mode(string $path): int {
        return fileperms($path) & 0x1FF;
    }

    public static function isEntry(string $path): bool {
        return is_file($path) || is_dir($path) || is_link($path) || self::isBlockDev($path) || self::isCharDev($path) || self::isNamedPipe($path) || self::isSocket($path);
    }

    public static function isBlockDev(string $path): bool {
        return @filetype($path) === 'block';
    }

    public static function isCharDev(string $path): bool {
        return @filetype($path) === 'char';
    }

    public static function isNamedPipe(string $path): bool {
        return @filetype($path) === 'fifo';
    }

    /* Use is_file()
    public static function isRegularFile(string $path): bool
    */

    /* Use is_dir()
    public static function isDirectory(string $path): bool
    */

    /* Use is_link()
    public static function isSymlink(string $path): bool {
    */

    public static function isSocket(string $path): bool {
        return @filetype($path) === 'socket';
    }
}