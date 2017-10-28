<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\EventManager as BaseEventManager;
use Morpho\Core\Module;

class EventManager extends BaseEventManager {
    protected $serviceManager;

    public function __construct($serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->attachHandlers();
    }

    protected function attachHandlers() {
        // Attach error handlers.
        $this->attachErrorHandlers();
        $this->attachViewHandlers();
    }

    private function attachErrorHandlers(): void {
        $this->on('dispatchError', function (Event $event) {
            $module = $this->moduleFromSetting('errorHandler');
            return $module->dispatchError($event);
        });
    }

    private function attachViewHandlers(): void {
        // Attach view handlers.
        $this->on('render', function (Event $event) {
            $module = $this->moduleFromSetting('errorHandler');
            /** @var View\View $view */
            $view = $event->args['view'];
            return $module->theme()->renderView($view);
        });
        $this->on('afterDispatch', function (Event $event) {
            $module = $this->moduleFromSetting('errorHandler');
            $module->afterDispatch($event);
        });
    }

    private function moduleFromSetting(string $setting): Module {
        $serviceManager = $this->serviceManager;
        $moduleProvider = $serviceManager->get('moduleProvider');
        $moduleName = $serviceManager->get('site')->moduleName();
        $moduleMeta = $serviceManager->get('moduleIndex')->moduleMeta($moduleName);
        $config = $moduleMeta['services']['eventManager'];
        $module = $moduleProvider->offsetGet($config[$setting]);
        return $module;
    }
}