<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\Event;
use Morpho\Base\EventManager as BaseEventManager;
use Morpho\Ioc\IServiceManager;

class EventManager extends BaseEventManager {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        $this->attachHandlers();
    }

    protected function attachHandlers() {
        $this->on('dispatchError', [$this, 'onDispatchError']);
        $this->on('afterDispatch', [$this, 'onAfterDispatch']);
    }

    protected function onDispatchError(Event $event): void {
        /** @var DispatchErrorHandler $dispatchErrorHandler */
        $dispatchErrorHandler = $this->serviceManager['dispatchErrorHandler'];
        $config = $this->serviceManager->config()['dispatchErrorHandler'];
        $dispatchErrorHandler->throwErrors($config['throwErrors']);
        if (isset($config['handlers'])) {
            foreach ($config['handlers'] as $errorType => $handler) {
                $dispatchErrorHandler->setHandler($errorType, $handler);
            }
        }
        /** @var Request $request */
        $request = $event->args['request'];
        $dispatchErrorHandler->handleError($event->args['exception'], $request);
        // $request->response()->isRendered(false);
    }

    protected function onAfterDispatch(Event $event): void {
        /** @var Request $request */
        $request = $event->args['request'];
        $actionResultRenderer = $this->serviceManager['actionResultRenderer'];
        $actionResultRenderer->__invoke($request);
    }
}
