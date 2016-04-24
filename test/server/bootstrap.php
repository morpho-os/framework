<?php
date_default_timezone_set('UTC');

(function () {
    $classLoader = require __DIR__ . '/../../vendor/autoload.php';
    $classLoader->add('MorphoTest', __DIR__ . '/unit');
    foreach (glob(MODULE_DIR_PATH . '/*') as $moduleDirPath) {
        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        if (is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
})();