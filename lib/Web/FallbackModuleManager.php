<?php
//declare(strict_types=1);
namespace Morpho\Web;

class FallbackModuleManager extends ModuleManager {
    protected $fallbackMode = false;

    protected $eventHandlers;

    public function allModuleNames(): array {
        return [];
    }

    function installedModuleNames(): array {
        return [];
    }

    function uninstalledModuleNames(): array {
        return [];
    }

    function enabledModuleNames(): array {
        return [];
    }

    function disabledModuleNames(): array {
        return [];
    }

    protected function initEventHandlers(): void {
        if (null !== $this->eventHandlers) {
            return;
        }
        $this->eventHandlers = $this->eventHandlers();
    }

    protected function eventHandlers(): array {
        return [
            'render'         => [
                [
                    'moduleName' => ModuleManager::SYSTEM_MODULE,
                    'method'     => 'render',
                ],
            ],
            'afterDispatch'  => [
                [
                    'moduleName' => ModuleManager::SYSTEM_MODULE,
                    'method'     => 'afterDispatch',
                ],
            ],
            'beforeDispatch' => [
                [
                    'moduleName' => ModuleManager::SYSTEM_MODULE,
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
}