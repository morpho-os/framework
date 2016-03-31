<?php
use Morpho\Core\ModuleClassLoader;
use Morpho\Core\ModuleListProvider;
use Morpho\Core\ModulePathManager;
use Morpho\Web\ModuleManager;

date_default_timezone_set('UTC');

(function () {
    $classLoader = require __DIR__ . '/../../vendor/autoload.php';
    $classLoader->add('MorphoTest', __DIR__ . '/unit');
    $modulePathManager = new ModulePathManager(MODULE_DIR_PATH);
    $moduleClassLoader = new ModuleClassLoader($modulePathManager, null, false);
    $moduleManager = new ModuleManager(null, new ModuleListProvider($modulePathManager), $moduleClassLoader);
    foreach ($moduleManager->listAllModules() as $moduleName) {
        $moduleClassLoader->registerModule($moduleName);
    }
    $moduleClassLoader->register();
})();