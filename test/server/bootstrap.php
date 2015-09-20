<?php
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../vendor/autoload.php';
(new \Morpho\Base\ClassLoader())
    ->addPrefixToDirPathMappingPsr0('MorphoTest', __DIR__ . '/unit')
    ->register();
(new \Morpho\Core\ModuleAutoloader(MODULE_DIR_PATH, sys_get_temp_dir(), false))->register();