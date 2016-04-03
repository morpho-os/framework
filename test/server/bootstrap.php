<?php
use Morpho\Core\ModuleAutoloader;
use Morpho\Core\ModuleListProvider;
use Morpho\Core\ModulePathManager;
use Morpho\Web\ModuleManager;

date_default_timezone_set('UTC');

(function () {
    $classLoader = require __DIR__ . '/../../vendor/autoload.php';
    $classLoader->add('MorphoTest', __DIR__ . '/unit');
    $modulePathManager = new ModulePathManager(MODULE_DIR_PATH);
    $autoloader = new ModuleAutoloader($modulePathManager, null, false);
    $moduleManager = new ModuleManager(null, new ModuleListProvider($modulePathManager), $autoloader);
    foreach ($moduleManager->listAllModules() as $moduleName) {
        $autoloader->registerModule($moduleName);
    }
    $autoloader->register();
})();