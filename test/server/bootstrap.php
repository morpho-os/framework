<?php
date_default_timezone_set('UTC');

$autoloader = require __DIR__ . '/../../vendor/autoload.php';
$autoloader->add('MorphoTest\\', __DIR__ . '/unit');
(new \Morpho\Core\ModuleAutoloader(MODULE_DIR_PATH, sys_get_temp_dir(), false))->register();
