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
    
    public static function isBroken(string $linkPath): bool {
        if (!is_link($linkPath)) {
            throw new Exception("The '$linkPath' is not a link");
        }
        $targetPath = readlink($linkPath);
        if (false === $targetPath) {
            return false;
        }
        if (Path::isAbsolute($targetPath)) {
            return !self::isEntry($targetPath);
        }
        $curDirPath = getcwd();
        chdir(dirname($linkPath));
        try {
            return !self::isEntry($targetPath);
        } finally {
            chdir($curDirPath);
        }
    }
}
