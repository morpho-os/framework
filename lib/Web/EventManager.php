<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\EventManager as BaseEventManager;

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
            /** @var DispatchErrorHandler $dispatchErrorHandler */
            $dispatchErrorHandler = $this->serviceManager->get('dispatchErrorHandler');
            $config = $this->serviceManager->config()['dispatchErrorHandler'];
            $dispatchErrorHandler->throwErrors($config['throwErrors']);
            if (isset($config['handlers'])) {
                foreach ($config['handlers'] as $errorType => $handler) {
                    $dispatchErrorHandler->setHandler($errorType, $handler);
                }
            }
            $dispatchErrorHandler->handleError($event->args['exception'], $event->args['request']);
        });
    }

    private function attachViewHandlers(): void {
        $this->on('render', function (Event $event) {
            $serviceManager = $this->serviceManager;

            $moduleName = $serviceManager->get('request')->moduleName();
            $moduleIndex = $serviceManager->get('moduleIndex');
            $viewDirPath = $moduleIndex->moduleMeta($moduleName)->viewDirPath();
            /** @var View\Theme $theme */
            $theme = $serviceManager->get('theme');
            $theme->appendBaseDirPath($viewDirPath);

            $view = $event->args['view'];
            return $theme->renderView($view);
        });

        $this->on('afterDispatch', function (Event $event) {
            $serviceManager = $this->serviceManager;
            $moduleName = $serviceManager->config()['eventManager']['layoutModule'];
            /** @var View\Theme $theme */
            $theme = $serviceManager->get('theme');
            $viewDirPath = $serviceManager->get('moduleIndex')->moduleMeta($moduleName)->viewDirPath();
            $theme->appendBaseDirPath($viewDirPath);

            $request = $event->args['request'];
            $theme->renderLayout($request);
        });
    }
}