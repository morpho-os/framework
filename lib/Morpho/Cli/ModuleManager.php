<?php
namespace Morpho\Cli;

use Morpho\Base\NotImplementedException;
use Morpho\Core\ModuleManager as BaseModuleManager;

class ModuleManager extends BaseModuleManager {
    protected function actionNotFound($moduleName, $controllerName, $actionName) {
        throw new NotImplementedException();
    }
}