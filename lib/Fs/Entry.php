<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

use function is_dir;
use function is_iterable;

abstract class Entry {
    public static function copy(string $srcPath, string $destPath): string {
        return is_dir($srcPath)
            ? Dir::copy($srcPath, $destPath)
            : File::copy($srcPath, $destPath);
    }

    public static function delete(string|iterable $entryPath): void {
        if (is_iterable($entryPath)) {
            foreach ($entryPath as $path) {
                static::delete($path);
            }
            return;
        }
        if (is_dir($entryPath)) {
            Dir::delete($entryPath);
        } else {
            File::delete($entryPath);
        }
    }
}
