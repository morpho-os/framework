<?php
namespace Morpho\Web;

use Morpho\Core\ModuleManager as BaseModuleManager;

class ModuleManager extends BaseModuleManager {
    protected function actionNotFound($moduleName, $controllerName, $actionName): void {
        $message = [];
        if (empty($moduleName)) {
            $message[] = 'module name is empty';
        }
        if (empty($controllerName)) {
            $message[] = 'controller name is empty';
        }
        if (empty($actionName)) {
            $message[] = 'action name is empty';
        }
        if (!count($message)) {
            $message[] = 'unknown';
        }
        throw new NotFoundException("Reason: " . implode(", ", $message));
    }
}