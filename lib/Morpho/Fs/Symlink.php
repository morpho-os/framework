<?php
namespace Morpho\Fs;

class Symlink extends Entry {
    public static function create($targetPath, $linkPath) {
        Directory::create(dirname($linkPath));
        if (is_file($targetPath) && is_dir($linkPath)) {
            $linkPath = $linkPath . '/' . basename($targetPath);
        }
        if (!@symlink($targetPath, $linkPath)) {
            throw new Exception("Unable to create symlink '$linkPath' for target '$targetPath'");
        }
    }
}
