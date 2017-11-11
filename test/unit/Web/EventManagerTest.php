<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Base\Event;
use Morpho\Core\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\DispatchErrorHandler;
use Morpho\Web\EventManager;
use Morpho\Web\Request;

class EventManagerTest extends TestCase {
    public function testDispatchErrorEventHandling() {
        $serviceManager = $this->createMock(ServiceManager::class);
        $throwErrors = true;
        $handlers = [
            DispatchErrorHandler::ACCESS_DENIED_ERROR => ['a', 'b', 'c'],
            DispatchErrorHandler::BAD_REQUEST_ERROR => ['d', 'e', 'f'],
            DispatchErrorHandler::NOT_FOUND_ERROR => ['g', 'h', 'i'],
            DispatchErrorHandler::UNCAUGHT_ERROR => ['j', 'k', 'l'],
        ];
        $config = [
            'dispatchErrorHandler' => [
                'throwErrors' => $throwErrors,
                'handlers' => $handlers,
            ],
        ];
        $exception = new \RuntimeException();
        $request = $this->createMock(Request::class);
        $dispatchErrorHandler = $this->createMock(DispatchErrorHandler::class);
        $dispatchErrorHandler->expects($this->once())
            ->method('throwErrors')
            ->with($this->identicalTo($throwErrors));
        $dispatchErrorHandler->expects($this->exactly(count($handlers)))
            ->method('setHandler')
            ->withConsecutive(
                [$this->identicalTo(DispatchErrorHandler::ACCESS_DENIED_ERROR), $this->identicalTo($handlers[DispatchErrorHandler::ACCESS_DENIED_ERROR])],
                [$this->identicalTo(DispatchErrorHandler::BAD_REQUEST_ERROR), $this->identicalTo($handlers[DispatchErrorHandler::BAD_REQUEST_ERROR])],
                [$this->identicalTo(DispatchErrorHandler::NOT_FOUND_ERROR), $this->identicalTo($handlers[DispatchErrorHandler::NOT_FOUND_ERROR])],
                [$this->identicalTo(DispatchErrorHandler::UNCAUGHT_ERROR), $this->identicalTo($handlers[DispatchErrorHandler::UNCAUGHT_ERROR])]
            );
        $dispatchErrorHandler->expects($this->once())
            ->method('handleError')
            ->with($this->identicalTo($exception), $this->identicalTo($request));
        $serviceManager->expects($this->any())
            ->method('config')
            ->willReturn($config);
        $serviceManager->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('dispatchErrorHandler'))
            ->will($this->returnValue($dispatchErrorHandler));
        $eventManager = new EventManager($serviceManager);
        $event = new Event('dispatchError', ['exception' => $exception, 'request' => $request]);

        $eventManager->trigger($event);
    }
}