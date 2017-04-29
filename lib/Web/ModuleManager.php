<?php
namespace Morpho\Web;

use Morpho\Core\ModuleManager as BaseModuleManager;

class ModuleManager extends BaseModuleManager {
    const SYSTEM_MODULE    = 'morpho-os/system';
    const USER_MODULE      = 'morpho-os/user';
    const BOOTSTRAP_MODULE = 'morpho-os/bootstrap';

    protected $fallbackModules = [
        self::SYSTEM_MODULE,
        self::USER_MODULE,
        self::BOOTSTRAP_MODULE,
    ];

    protected function fallbackModeEventHandlers(): array {
        return [
            'render'         => [
                [
                    'moduleName' => self::BOOTSTRAP_MODULE,
                    'method'     => 'render',
                ],
            ],
            'afterDispatch'  => [
                [
                    'moduleName' => self::BOOTSTRAP_MODULE,
                    'method'     => 'afterDispatch',
                ],
            ],
            'beforeDispatch' => [
                [
                    'moduleName' => self::BOOTSTRAP_MODULE,
                    'method'     => 'beforeDispatch',
                ],
            ],
            'dispatchError'  => [
                function ($event) {
                    throw $event[1]['exception'];
                },
            ],
        ];
    }

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