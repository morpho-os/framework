<?php
namespace Morpho\Fs;

abstract class Entry {
    /**
     * @return int
     */
    public static function mode($path) {
        // @TODO: Handle errors.
        return octdec(substr(sprintf('%o', fileperms($path)), -4));
    }
}
