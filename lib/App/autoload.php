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

const VENDOR = 'morpho-os';

const BIN_DIR_NAME = 'bin';
const CACHE_DIR_NAME = 'cache';
const FRONTEND_DIR_NAME = 'frontend';
const CONF_DIR_NAME = 'conf';
const LIB_DIR_NAME = 'lib';
const LOG_DIR_NAME = 'log';
const RC_DIR_NAME = 'rc';
const BACKEND_DIR_NAME = 'backend';
const TEST_DIR_NAME = 'test';
const TMP_DIR_NAME = 'tmp';
const VENDOR_DIR_NAME = 'vendor';
const VIEW_DIR_NAME = 'view';

const AUTOLOAD_FILE_NAME = 'autoload.php';
const META_FILE_NAME = 'composer.json';
const SITE_CONF_FILE_NAME = 'site.conf.php';

//const DOMAIN_NS = 'Domain';

// Often used class names' suffixes:
const CONTROLLER_SUFFIX = 'Controller';
const PLUGIN_SUFFIX = 'Plugin';
const REPO_SUFFIX = 'Repo';
const MANAGER_SUFFIX = 'Manager';
const SERVICE_SUFFIX = 'Service';
