<?php
namespace Morpho\Fs;

use Symfony\Component\Finder\Finder;

abstract class Entry {
    public static function find(): Finder {
        return new Finder();
    }

    /**
     * @param iterable|string $entryPath
     */
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
