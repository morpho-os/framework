<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Fs;

use function clearstatcache;
use function file_exists;
use function fileperms;
use function filetype;
use function sprintf;

class Stat {
    // Changed file types from /usr/include/bits/stat.h
    // ENTRY = DIR | CHAR_DEV | BLOCK_DEV | REG_FILE | FIFO | SYMLINK | SOCKET
    public const ENTRY     = 0170000;                  // Any file system entry (mask to extract file type, S_IFMT)
    public const DIR       = 0040000;                  // Directory
    public const CHAR_DEV  = 0020000;                  // Character device
    public const BLOCK_DEV = 0060000;                  // Block device
    public const FILE      = 0100000;                  // Regular file
    public const FIFO      = 0010000;                  // FIFO
    public const LINK      = 0120000;                  // Symbolic link
    public const SOCKET    = 0140000;                  // Socket
    public const NOT_DIR   = self::ENTRY ^ self::DIR;  // Anything except directory.

    public const DIR_BASE_MODE = 0777;
    public const FILE_BASE_MODE = 0666;

    public const UMASK = 0022;

    public const DIR_MODE  = 0755; // DIR_BASE_MODE (0777)  - UMASK (0022) ~> DIR_MODE
    public const FILE_MODE = 0644; // FILE_BASE_MODE (0666) - UMASK (0022) ~> FILE_MODE

    public static function modeToStr(int $mode): string {
        return sprintf('%04o', $mode & 07777);
    }

    /**
     * Returns value of bits [11..0] of the stat.st_mode as string.
     */
    public static function modeStr(string $path): string {
        return sprintf('%04o', self::mode($path));
    }

    public static function mode(string $path): int {
        clearstatcache(true, $path);
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

    /* Use \is_file()
    public static function isRegularFile(string $path): bool
    */

    /* Use \is_dir()
    public static function isDirectory(string $path): bool
    */

    /* Use \is_link()
    public static function isLink(string $path): bool {
    */

    public static function isSocket(string $path): bool {
        return filetype($path) === 'socket';
    }
}
