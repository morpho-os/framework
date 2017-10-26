<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\EventManager as BaseEventManager;
use const Morpho\Core\VENDOR;

class EventManager extends BaseEventManager {
    private const SYSTEM_MODULE = VENDOR . '/system';
    protected $serviceManager;

    public function __construct($serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->attachHandlers();
    }

    protected function attachHandlers() {
        $moduleProvider = $this->serviceManager->get('moduleProvider');
        /**
         * @var \Morpho\System\Web\Module $module
         */
        $module = $moduleProvider->offsetGet(self::SYSTEM_MODULE);
        $this->on('dispatchError', function (Event $event) use ($module) {
            return $module->dispatchError($event);
        });
        $this->on('render', function (Event $event) use ($module) {
            /** @var View\View $view */
            $view = $event->args['view'];
            return $module->theme()->renderView($view);
        });
        $this->on('afterDispatch', function (Event $event) use ($module) {
            $module->afterDispatch($event);
        });
    }
}