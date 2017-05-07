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

    /**
     * @return bool Returns true if the $path is valid path of any of: Directory, Character device, Block device, Regular file, FIFO/Named pipe, Symbolic link, Socket.
     */
    public static function isEntry(string $path): bool {
        return file_exists($path);
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