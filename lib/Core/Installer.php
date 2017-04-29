<?php
namespace Morpho\Core;

use Morpho\Fs\Directory;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Composer\Script\Event;
use const Morpho\Web\PUBLIC_DIR_NAME;

require_once __DIR__ . '/../Core/autoload.php';

class Installer {
    public static function postInstall(Event $event) {
        self::initDirectories($event);
    }

    public static function postUpdate(Event $event) {
        self::initDirectories($event);
    }

    protected static function initDirectories(Event $event) {
        /*
        if (self::isTravisEnv()) {
            return;
        }
        */
        $vendorDirPath = self::findVendorDirPath($event->getComposer()->getConfig()->get('vendor-dir'));
        $baseDirPath = Path::normalize(dirname($vendorDirPath));
        $frameworkBaseDirPath = self::findFrameworkBaseDirPath($vendorDirPath);

        $io = $event->getIO();

        $io->write("\n", false);

        self::processWebDir($frameworkBaseDirPath, $baseDirPath, $io);

        self::processSiteDir($frameworkBaseDirPath, $baseDirPath, $io);
    }

    /**
     * @return bool
     */
    protected static function isTravisEnv() {
        return getenv('TRAVIS');
    }

    protected static function findVendorDirPath($vendorDirName) {
        $vendorDirPath = realpath($vendorDirName);
        if (false === $vendorDirPath || !is_file($vendorDirPath . '/composer/ClassLoader.php')) {
            throw new \RuntimeException("Unable to detect path to vendor directory.");
        }
        return $vendorDirPath;
    }

    protected static function findFrameworkBaseDirPath($vendorDirPath) {
        if (!is_dir($vendorDirPath . '/morpho/framework')) {
            throw new \RuntimeException("Unable to detect morpho/framework directory.");
        }
        return $vendorDirPath . '/morpho/framework';
    }

    private static function processWebDir($frameworkBaseDirPath, $baseDirPath, $io) {
        $dirPaths = Directory::dirPaths(
            $frameworkBaseDirPath . '/' . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME,
            null,
            ['recursive' => false]
        );
        foreach ($dirPaths as $sourceDirPath) {
            $moduleDirName = basename($sourceDirPath);
            $targetDirPath = $baseDirPath . '/' . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME . '/' . $moduleDirName;
            if (is_dir($targetDirPath)) {
                $io->write(
                    "Deleting the old '" . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME . '/' . $moduleDirName . "' directory... ",
                    false
                );
                Directory::delete($targetDirPath);
                $io->write("OK", true);
            }

            // @TODO: Skip, don't copy tests.
            $io->write("Copying the '$moduleDirName/' directory... ", false);
            Directory::copy($sourceDirPath, $targetDirPath);
            $io->write("OK\n", true);
        }

        $filePaths = [
            'index.php',
            '.htaccess',
            MODULE_DIR_NAME . '/.htaccess',
        ];
        foreach ($filePaths as $filePath) {
            $targetFilePath = $baseDirPath . '/' . PUBLIC_DIR_NAME . '/' . $filePath;
            if (!is_file($targetFilePath)) {
                $io->write("Copying the '" . PUBLIC_DIR_NAME . '/' . $filePath . "' file... ", false);
                File::copy(
                    $frameworkBaseDirPath . '/' . PUBLIC_DIR_NAME . '/' . $filePath,
                    $targetFilePath,
                    false,
                    true
                );
                $io->write("OK\n", true);
            }
        }
    }

    private static function processSiteDir($frameworkBaseDirPath, $baseDirPath, $io) {
        $dirNames = [
            CACHE_DIR_NAME,
            LOG_DIR_NAME,
        ];
        foreach ($dirNames as $dirName) {
            $targetDirPath = $baseDirPath . '/' . MODULE_DIR_NAME . '/default/' . $dirName;
            if (is_dir($targetDirPath)) {
                $io->write("Deleting the old '" . MODULE_DIR_NAME . '/default/' . $dirName . "/' directory... ", false);
                Directory::delete($targetDirPath);
                $io->write("OK", true);
            }

            $io->write("Creating the '" . MODULE_DIR_NAME . '/default/' . $dirName . "' directory... ", false);
            Directory::create($targetDirPath, 750);
            $io->write("OK\n", true);
        }

        $targetDirPath = $baseDirPath . '/' . MODULE_DIR_NAME . '/default/' . CONFIG_DIR_NAME;
        if (!is_dir($targetDirPath)) {
            $io->write("Creating the '" . MODULE_DIR_NAME . '/default/' . CONFIG_DIR_NAME . "' directory... ", false);
            Directory::copy(
                $frameworkBaseDirPath . '/' . MODULE_DIR_NAME . '/default/' . CONFIG_DIR_NAME,
                $targetDirPath
            );
            $io->write("OK", true);
        }
    }
}