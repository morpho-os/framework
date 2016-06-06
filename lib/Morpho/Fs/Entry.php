<?php
namespace Morpho\Fs;

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
}
