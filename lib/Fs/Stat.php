<?php
declare(strict_types=1);

namespace Morpho\Fs;

class Stat {
    // Changed file types from /usr/include/bits/stat.h
    // ENTRY = DIR | CHAR_DEV | BLOCK_DEV | REG_FILE | FIFO | SYMLINK | SOCKET
    public const ENTRY     = 0170000;                  // Any file system entry
    public const DIR       = 0040000;                  // Directory
    public const CHAR_DEV  = 0020000;                  // Character device
    public const BLOCK_DEV = 0060000;                  // Block device
    public const FILE      = 0100000;                  // Regular file
    public const FIFO      = 0010000;                  // FIFO
    public const SYMLINK   = 0120000;                  // Symbolic link
    public const SOCKET    = 0140000;                  // Socket
    public const NOT_DIR   = self::ENTRY ^ self::DIR;  // Anything except directory.

    // @TODO: sync with umask() calls
    public const DIR_MODE  = 0755;
    public const FILE_MODE = 0644;

    public static function modeString(string $path): string {
        return sprintf('%o', self::mode($path));
    }

    public static function mode(string $path): int {
        clearstatcache();
        return fileperms($path) & 07777;
    }

    /**
     * @return bool Returns true if the $path is valid path of any of: Directory, Character device, Block device, Regular file, FIFO/Named pipe, Symbolic link, Socket.
     */
    public static function isEntry(string $path): bool {
        return file_exists($path);
    }

    public static function isBlockDev(string $path): bool {
        return filetype($path) === 'block';
    }

    public static function isCharDev(string $path): bool {
        return filetype($path) === 'char';
    }

    public static function isNamedPipe(string $path): bool {
        return filetype($path) === 'fifo';
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
        return filetype($path) === 'socket';
    }
}