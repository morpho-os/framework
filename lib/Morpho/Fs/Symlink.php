<?php
namespace Morpho\Fs;

class Symlink extends Entry {
    public static function create($linkPath, $targetPath) {
        Directory::create(dirname($linkPath));
        if (!@symlink($targetPath, $linkPath)) {
            throw new \RuntimeException("Unable to create symlink '$linkPath' for target '$targetPath'");
        }
    }
}
