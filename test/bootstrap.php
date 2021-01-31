<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use const Morpho\App\{AUTOLOAD_FILE_NAME, VENDOR_DIR_NAME};
use Morpho\Testing\Sut;

\date_default_timezone_set('UTC');

(function () {
    /*
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
    $classLoader->addPsr4(__NAMESPACE__ . '\\', __DIR__);
    */

    foreach (Sut::instance()->backendModuleIterator() as $moduleDirPath) {
        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        if (\is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
})();
