<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

//if (PHP_SAPI == 'cli') {
    require __DIR__ . '/Cli/autoload.php';
//} else {
    require __DIR__ . '/Web/autoload.php';
//}

use Morpho\Fs\Path;
const VENDOR = 'morpho-os';

const APP_DIR_NAME = 'app';
const BIN_DIR_NAME = 'bin';
const CACHE_DIR_NAME = 'cache';
const CONFIG_DIR_NAME = 'config';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const MODULE_DIR_NAME = 'module';
const RC_DIR_NAME = 'rc';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';
const VENDOR_DIR_NAME = 'vendor';
const VIEW_DIR_NAME = 'view';

const AUTOLOAD_FILE_NAME = 'autoload.php';
const META_FILE_NAME = 'composer.json';
//const MODULE_CLASS_FILE_NAME = 'Module.php';

const DOMAIN_NS = 'Domain';

const ACTION_SUFFIX = 'Action';
const CONTROLLER_SUFFIX = 'Controller';
//const MODULE_SUFFIX = 'Module';
const PLUGIN_SUFFIX = 'Plugin';
const REPO_SUFFIX = 'Repo';

/**
 * Detects and returns base directory path of the module.
 * @param string $dirPath Any directory path within the module.
 * @return false|string
 */
function moduleDirPath(string $dirPath, bool $throwEx = true) {
    $baseDirPath = false;
    do {
        $path = $dirPath . '/vendor/composer/ClassLoader.php';
        if (\is_file($path)) {
            $baseDirPath = $dirPath;
            break;
        } else {
            $chunks = \explode(DIRECTORY_SEPARATOR, $dirPath, -1);
            $dirPath = \implode(DIRECTORY_SEPARATOR, $chunks);
        }
    } while ($chunks);
    if (false === $baseDirPath) {
        if ($throwEx) {
            throw new \RuntimeException("Unable to detect the base directory of a module");
        }
        return false;
    }
    return Path::normalize($baseDirPath);
}
