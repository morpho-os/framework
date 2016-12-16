<?php
namespace Morpho\Fs;

use Symfony\Component\Finder\Finder;

abstract class Entry {
    /**
     * @return int
     */
    public static function mode(string $path) {
        // @TODO: Handle errors.
        return octdec(substr(sprintf('%o', fileperms($path)), -4));
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
