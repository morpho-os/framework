<?php
// @codingStandardsIgnoreFile
use Morpho\Fs\Path;

if (!defined('BASE_DIR_PATH')) {
    define('BASE_DIR_PATH', Path::detectBaseProjectDirPath(__DIR__));
}
if (!Path::isNormalized(BASE_DIR_PATH)) {
    throw new \RuntimeException("The 'BASE_DIR_PATH' must be normalized: replace backslashes with forward slashes and remove the last right slash.");
}

const WEB_DIR_NAME = 'web';
if (!defined('WEB_DIR_PATH')) {
    define('WEB_DIR_PATH', BASE_DIR_PATH . '/' . WEB_DIR_NAME);
}
if (PHP_SAPI !== 'cli' && !chdir(WEB_DIR_PATH)) {
    throw new \RuntimeException("Unable to change directory to the web directory path.");
}
const APP_DIR_NAME = 'app';
const CACHE_DIR_NAME = 'cache';
const CONFIG_DIR_NAME = 'config';
const CONTROLLER_DIR_NAME = 'controller';
const CSS_DIR_NAME = 'css';
const DOMAIN_DIR_NAME = 'domain';
const FORM_DIR_NAME = 'form';
const IMG_DIR_NAME = 'img';
const JS_DIR_NAME = 'js';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const MODULE_DIR_NAME = 'module';
const RESOURCE_DIR_NAME = 'resource';
const SCRIPT_DIR_NAME = 'script';
const SITE_DIR_NAME = 'site';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';
const UPLOAD_DIR_NAME = 'upload';
const VIEW_DIR_NAME = 'view';

const DOMAIN_NS = 'Domain';
const REPO_SUFFIX = 'Repo';
const MODULE_SUFFIX = 'Module';
const CONTROLLER_NS = 'Controller';
const CONTROLLER_SUFFIX = 'Controller';
const ACTION_SUFFIX = 'Action';
const APP_NS = 'App';

define('LIB_DIR_PATH', BASE_DIR_PATH . '/' . LIB_DIR_NAME);
define('MODULE_DIR_PATH', BASE_DIR_PATH . '/' . MODULE_DIR_NAME);
define('RESOURCE_DIR_PATH', WEB_DIR_PATH . '/' . RESOURCE_DIR_NAME);
define('SITE_DIR_PATH', BASE_DIR_PATH . '/' . SITE_DIR_NAME);

// Can be used in comparison operations with real numbers.
const EPS = 0.00001;
