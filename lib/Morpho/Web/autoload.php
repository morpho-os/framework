<?php
const PUBLIC_DIR_NAME = 'public';
if (!defined('PUBLIC_DIR_PATH')) {
    define('PUBLIC_DIR_PATH', BASE_DIR_PATH . '/' . PUBLIC_DIR_NAME);
}
if (PHP_SAPI !== 'cli' && !chdir(PUBLIC_DIR_PATH)) {
    throw new \RuntimeException("Unable to change directory to the web directory path.");
}

const CSS_DIR_NAME = 'css';
const DEST_DIR_NAME = 'dest';
const DOMAIN_DIR_NAME = 'domain';
const FORM_DIR_NAME = 'form';
const IMG_DIR_NAME = 'img';
const JS_DIR_NAME = 'js';
const PUBLIC_MODULE_DIR_NAME = 'module';
const SCRIPT_DIR_NAME = 'script';
const SRC_DIR_NAME = 'src';
const STYL_DIR_NAME = 'styl';
const TS_DIR_NAME = 'ts';
const UPLOAD_DIR_NAME = 'upload';
const VIEW_DIR_NAME = 'view';

const PUBLIC_MODULE_DIR_PATH = PUBLIC_DIR_PATH . '/' . PUBLIC_MODULE_DIR_NAME;