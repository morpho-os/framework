<?php declare(strict_types=1);
namespace MorphoTest;

use const Morpho\Core\AUTOLOAD_FILE_NAME;
use const Morpho\Core\MODULE_DIR_PATH;
use const Morpho\Core\VENDOR_DIR_NAME;

date_default_timezone_set('UTC');

(function () {
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
    $classLoader->addPsr4('MorphoTest\\', __DIR__ . '/server/unit');

    foreach (glob(MODULE_DIR_PATH . '/*') as $moduleDirPath) {
        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        if (is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
})();
