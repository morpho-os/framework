<?php
define('BASE_DIR_PATH', str_replace('\\', '/', dirname(__DIR__)));
require BASE_DIR_PATH . '/vendor/autoload.php';
(new \Morpho\Modulizer\Modulizer)();