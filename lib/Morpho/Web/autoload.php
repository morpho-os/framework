<?php
// Below are some constants for the web-application-level, see the Morpho/Core/autoload.php for the Core-level constants.

const PUBLIC_DIR_NAME = 'public';
if (!defined('PUBLIC_DIR_PATH')) {
    define('PUBLIC_DIR_PATH', BASE_DIR_PATH . '/' . PUBLIC_DIR_NAME);
}
if (PHP_SAPI !== 'cli' && !chdir(PUBLIC_DIR_PATH)) {
    throw new \RuntimeException("Unable to change directory to the web directory path.");
}

const CSS_DIR_NAME = 'css';
//const DEST_DIR_NAME = 'dest';
const DOMAIN_DIR_NAME = 'domain';
const FONT_DIR_NAME = 'font';
const IMG_DIR_NAME = 'img';
const JS_DIR_NAME = 'js';
const PUBLIC_MODULE_DIR_NAME = 'module';
const PUBLIC_MODULE_DIR_PATH = PUBLIC_DIR_PATH . '/' . PUBLIC_MODULE_DIR_NAME;
//const RC_DIR_NAME = 'rc';
const SCRIPT_DIR_NAME = 'script';
const SITE_DIR_NAME = MODULE_DIR_NAME;
const SITE_DIR_PATH =  BASE_DIR_PATH . '/' . SITE_DIR_NAME;
const SRC_DIR_NAME = 'src';
const UPLOAD_DIR_NAME = 'upload';
const VIEW_DIR_NAME = 'view';