<?php
namespace Morpho\Web;

use Morpho\Core\ModuleManager as BaseModuleManager;

class ModuleManager extends BaseModuleManager {
    protected function getFallbackModeEventHandlers(): array {
        return [
            'render' => [
                [
                    'moduleName' => 'Bootstrap',
                    'method' => 'render',
                ],
            ],
            'afterDispatch' => [
                [
                    'moduleName' => 'Bootstrap',
                    'method' => 'afterDispatch',
                ],
            ],
            'beforeDispatch' => [
                [
                    'moduleName' => 'Bootstrap',
                    'method' => 'beforeDispatch',
                ],
            ],
            'dispatchError' => [
                function ($event) {
                    throw $event[1]['exception'];
                }
            ],
        ];
    }

    protected function actionNotFound($moduleName, $controllerName, $actionName) {
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