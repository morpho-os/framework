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

    protected function attachHandlers(): void {
        $this->on('afterDispatch', [$this, 'handleActionResult']);
        $this->on('dispatchError', [$this, 'handleDispatchError']);
    }

    protected function handleActionResult(Event $event): void {
        /** @var Request $request */
        $request = $event->args['request'];
        $actionResultHandler = $this->serviceManager['actionResultHandler'];
        $actionResultHandler->__invoke($request);
    }

    protected function handleDispatchError(Event $event): void {
        /** @var DispatchErrorHandler $dispatchErrorHandler */
        $dispatchErrorHandler = $this->serviceManager['dispatchErrorHandler'];
        /** @var Request $request */
        $request = $event->args['request'];
        $dispatchErrorHandler->handleException($event->args['exception'], $request);
    }
}
