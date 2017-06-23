<?php
namespace Morpho\Web;

use Morpho\Core\ModuleManager as BaseModuleManager;
use const Morpho\Core\VENDOR;

class ModuleManager extends BaseModuleManager {
    const SYSTEM_MODULE    = VENDOR . '/system';
    const USER_MODULE      = VENDOR . '/user';

    protected $fallbackModules = [
        self::SYSTEM_MODULE,
        self::USER_MODULE,
    ];

    protected function fallbackModeEventHandlers(): array {
        return [
            'render'         => [
                [
                    'moduleName' => self::SYSTEM_MODULE,
                    'method'     => 'render',
                ],
            ],
            'afterDispatch'  => [
                [
                    'moduleName' => self::SYSTEM_MODULE,
                    'method'     => 'afterDispatch',
                ],
            ],
            'beforeDispatch' => [
                [
                    'moduleName' => self::SYSTEM_MODULE,
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