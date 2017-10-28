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
            $serviceManager = $this->serviceManager;
            $siteModuleName = $serviceManager->get('site')->moduleName();
            $moduleMeta = $serviceManager->get('moduleIndex')->moduleMeta($siteModuleName);
            $config = $moduleMeta['services']['eventManager'];

            $moduleProvider = $serviceManager->get('moduleProvider');
            $errorHandlerModule = $moduleProvider->offsetGet($config['errorHandler']);
            return $errorHandlerModule->dispatchError($event);
        });
    }

    private function attachViewHandlers(): void {
        $this->on('render', function (Event $event) {
            $view = $event->args['view'];
            /** @var View\Theme $theme */
            $theme = $this->serviceManager->get('theme');
            return $theme->renderView($view);
        });
        $this->on('afterDispatch', function (Event $event) {
            $serviceManager = $this->serviceManager;

            $siteModuleName = $serviceManager->get('site')->moduleName();
            $moduleIndex = $serviceManager->get('moduleIndex');
            $moduleMeta = $moduleIndex->moduleMeta($siteModuleName);
            $config = $moduleMeta['services']['eventManager'];
            $moduleName = $config['layoutHandler'];

            /** @var View\Theme $theme */
            $theme = $this->serviceManager->get('theme');
            $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
            $theme->appendBaseDirPath($viewDirPath);

            $request = $event->args['request'];
            $theme->renderLayout($request);
        });
    }
}