<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use Symfony\Component\Finder\Finder;

abstract class Entry {
    public static function find(): Finder {
        return new Finder();
    }

    public static function copy(string $srcPath, string $destPath): string {
        return is_dir($srcPath)
            ? Directory::copy($srcPath, $destPath)
            : File::copy($srcPath, $destPath);
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
