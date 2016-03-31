<?php
use Morpho\Core\Application;
use Morpho\Fs\Path;

if (!defined('BASE_DIR_PATH')) {
    define('BASE_DIR_PATH', Application::detectBaseDirPath(__DIR__));
}
if (!Path::isNormalized(BASE_DIR_PATH)) {
    throw new \RuntimeException("The 'BASE_DIR_PATH' must be normalized: replace backslashes with forward slashes and remove the last right slash.");
}

const APP_DIR_NAME = 'app';
const CACHE_DIR_NAME = 'cache';
const CONFIG_DIR_NAME = 'config';
const CONTROLLER_DIR_NAME = 'controller';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const MODULE_DIR_NAME = 'module';
const SITE_DIR_NAME = 'site';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';

const LIB_DIR_PATH = BASE_DIR_PATH . '/' . LIB_DIR_NAME;
const MODULE_DIR_PATH = BASE_DIR_PATH . '/' . MODULE_DIR_NAME;
const SITE_DIR_PATH = BASE_DIR_PATH . '/' . SITE_DIR_NAME;
const TEST_DIR_PATH = BASE_DIR_PATH . '/' . TEST_DIR_NAME;

const ACTION_SUFFIX = 'Action';
const APP_NS = 'App';
const CONTROLLER_NS = 'Controller';
const CONTROLLER_SUFFIX = 'Controller';
const DOMAIN_NS = 'Domain';
const MODULE_SUFFIX = 'Module';
const REPO_SUFFIX = 'Repo';
const PLUGIN_SUFFIX = 'Plugin';

// Can be used in comparison operations with real numbers.
const EPS = 0.00001;

const CONFIG_FILE_NAME = 'config.php';

const MODULE_CLASS_FILE_NAME = 'Module.php';

const COMPOSER_FILE_NAME = 'composer.json';
const COMPOSER_AUTOLOAD_FILE_PATH = 'vendor/autoload.php';