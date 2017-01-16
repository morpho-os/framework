<?php
namespace Morpho\Fs;

use Symfony\Component\Finder\Finder;

abstract class Entry {
    public static function modeString(string $path): string {
        return sprintf('%o', self::mode($path));
    }

    public static function mode(string $path): int {
        return fileperms($path) & 0x1FF;
    }

    public static function isEntry(string $path): bool {
        return is_file($path) || is_dir($path) || is_link($path);
    }

    public static function find(): Finder {
        return new Finder();
    }

    public static function delete(string $path) {
        if (is_dir($path)) {
            Directory::delete($path);
        } else {
            File::delete($path);
        }
    }
}
