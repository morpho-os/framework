<?php
use Morpho\Fs\Path;

/**
 * @return false|string
 */
function findVendorDirPath() {
    throw new \Morpho\Base\NotImplementedException();
}

/**
 * @TODO: Use findVendorDir()
 * @return false|string
 */
function findBaseDirPath(string $dirPath = null, bool $throwEx = true) {
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
            throw new \RuntimeException("Unable to find path of root directory.");
        }
        return null;
    }
    return Path::normalize($baseDirPath);
}

if (!defined('BASE_DIR_PATH')) {
    define('BASE_DIR_PATH', findBaseDirPath(__DIR__));
}
if (!Path::isNormalized(BASE_DIR_PATH)) {
    throw new \RuntimeException("The 'BASE_DIR_PATH' must be normalized: replace backslashes with forward slashes and remove the last right slash.");
}

// @TODO: Move constants under the Morpho\Core namespace.
const APP_DIR_NAME = 'app';
const BIN_DIR_NAME = 'bin';
const CACHE_DIR_NAME = 'cache';
const CONFIG_DIR_NAME = 'config';
const CONTROLLER_DIR_NAME = 'controller';
const DEST_DIR_NAME = 'dest';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const MODULE_DIR_NAME = 'module';
const RC_DIR_NAME = 'rc';
const SRC_DIR_NAME = 'src';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';
const VENDOR_DIR_NAME = 'vendor';

const CONFIG_FILE_NAME = 'config.php';
const AUTOLOAD_FILE_NAME = 'autoload.php';
const MODULE_META_FILE_NAME = 'composer.json';
const MODULE_CLASS_FILE_NAME = 'Module.php';

define('LIB_DIR_PATH',  BASE_DIR_PATH . '/' . LIB_DIR_NAME);
define('MODULE_DIR_PATH',  BASE_DIR_PATH . '/' . MODULE_DIR_NAME);
define('TEST_DIR_PATH',  BASE_DIR_PATH . '/' . TEST_DIR_NAME);
define('VENDOR_DIR_PATH',  BASE_DIR_PATH . '/' . VENDOR_DIR_NAME);

const ACTION_SUFFIX = 'Action';
const APP_NS = 'App';
const CONTROLLER_NS = 'Controller';
const CONTROLLER_SUFFIX = 'Controller';
const DOMAIN_NS = 'Domain';
const MODULE_SUFFIX = 'Module';
const REPO_SUFFIX = 'Repo';
const PLUGIN_SUFFIX = 'Plugin';

// @TODO: Detect precise value.
// Can be used in comparison operations with real numbers.
const EPS = 0.00001;

const SYSTEM_MODULE = 'morpho-os/system';
