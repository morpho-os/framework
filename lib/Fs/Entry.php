<?php
namespace Morpho\Fs;

use Symfony\Component\Finder\Finder;

abstract class Entry {
    public static function modeString(string $entryPath): string {
        return sprintf('%o', self::mode($entryPath));
    }

    public static function mode(string $entryPath): int {
        return fileperms($entryPath) & 0x1FF;
    }

    public static function isEntry(string $entryPath): bool {
        return is_file($entryPath) || is_dir($entryPath) || is_link($entryPath);
    }

    public static function find(): Finder {
        return new Finder();
    }

    public static function delete($entryPath): void {
        if (is_iterable($entryPath)) {
            foreach ($entryPath as $path) {
                static::delete($path);
            }
            return;
        }
        if (is_dir($entryPath)) {
            Directory::delete($entryPath);
        } else {
            File::delete($entryPath);
        }
    }
}
