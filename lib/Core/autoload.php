<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Fs\Path;

/**
 * @return false|string
 */
function detectVendorDirPath() {
    throw new \Morpho\Base\NotImplementedException();
}

/**
 * @TODO: Use detectVendorDir()
 * @return false|string
 */
function detectBaseDirPath(string $dirPath, bool $throwEx = true) {
    if (null === $dirPath) {
        $dirPath = __DIR__;
    }
    $baseDirPath = null;
    do {
        $path = $dirPath . '/vendor/composer/ClassLoader.php';
        if (is_file($path)) {
            $baseDirPath = $dirPath;
            break;
        } else {
            $chunks = explode(DIRECTORY_SEPARATOR, $dirPath, -1);
            $dirPath = implode(DIRECTORY_SEPARATOR, $chunks);
        }
    } while ($chunks);
    if (null === $baseDirPath) {
        if ($throwEx) {
            throw new \RuntimeException("Unable to find a path of the root directory");
        }
        return null;
    }
    return Path::normalize($baseDirPath);
}

define(
    __NAMESPACE__ . '\\BASE_DIR_PATH',
    defined('BASE_DIR_PATH') ? BASE_DIR_PATH : detectBaseDirPath(__DIR__)
);
if (!Path::isNormalized(BASE_DIR_PATH)) {
    throw new \RuntimeException("The 'BASE_DIR_PATH' must be normalized: replace backslashes with forward slashes and remove the last right slash");
}

const APP_DIR_NAME = 'app';
const BIN_DIR_NAME = 'bin';
const CACHE_DIR_NAME = 'cache';
const CONFIG_DIR_NAME = 'config';
const CONTROLLER_DIR_NAME = 'Controller';
const DEST_DIR_NAME = 'dest';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const MODULE_DIR_NAME = 'module';
const RC_DIR_NAME = 'rc';
const SRC_DIR_NAME = 'src';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';
const VENDOR_DIR_NAME = 'vendor';
const VENDOR = 'morpho-os';

const CONFIG_FILE_NAME = 'config.php';
const AUTOLOAD_FILE_NAME = 'autoload.php';
const MODULE_META_FILE_NAME = 'composer.json';
const MODULE_CLASS_FILE_NAME = 'Module.php';

define(__NAMESPACE__ . '\\LIB_DIR_PATH',  BASE_DIR_PATH . '/' . LIB_DIR_NAME);
define(__NAMESPACE__ . '\\MODULE_DIR_PATH',  BASE_DIR_PATH . '/' . MODULE_DIR_NAME);
define(__NAMESPACE__ . '\\TEST_DIR_PATH',  BASE_DIR_PATH . '/' . TEST_DIR_NAME);
define(__NAMESPACE__ . '\\VENDOR_DIR_PATH',  BASE_DIR_PATH . '/' . VENDOR_DIR_NAME);

const ACTION_SUFFIX = 'Action';
const APP_NS = 'App';
const CONTROLLER_NS = 'Controller';
const CONTROLLER_SUFFIX = 'Controller';
const DOMAIN_NS = 'Domain';
const MODULE_SUFFIX = 'Module';
const REPO_SUFFIX = 'Repo';
const PLUGIN_SUFFIX = 'Plugin';