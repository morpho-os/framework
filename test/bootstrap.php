<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest;

use const Morpho\Core\AUTOLOAD_FILE_NAME;
use const Morpho\Core\MODULE_DIR_PATH;
use const Morpho\Core\VENDOR_DIR_NAME;

date_default_timezone_set('UTC');

(function () {
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
    $classLoader->addPsr4('MorphoTest\\Unit\\', __DIR__ . '/unit');
    $classLoader->addPsr4('MorphoTest\\Functional\\', __DIR__ . '/functional');

    foreach (glob(MODULE_DIR_PATH . '/*') as $moduleDirPath) {
        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        if (is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
})();
