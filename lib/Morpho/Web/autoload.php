<?php
const WEB_DIR_NAME = 'web';
if (!defined('WEB_DIR_PATH')) {
    define('WEB_DIR_PATH', BASE_DIR_PATH . '/' . WEB_DIR_NAME);
}
if (PHP_SAPI !== 'cli' && !chdir(WEB_DIR_PATH)) {
    throw new \RuntimeException("Unable to change directory to the web directory path.");
}

const CSS_DIR_NAME        = 'css';
const DOMAIN_DIR_NAME     = 'domain';
const FORM_DIR_NAME       = 'form';
const IMG_DIR_NAME        = 'img';
const JS_DIR_NAME         = 'js';
const RESOURCE_DIR_NAME   = 'resource';
const SCRIPT_DIR_NAME     = 'script';
const UPLOAD_DIR_NAME     = 'upload';
const VIEW_DIR_NAME       = 'view';

const RESOURCE_DIR_PATH = WEB_DIR_PATH . '/' . RESOURCE_DIR_NAME;