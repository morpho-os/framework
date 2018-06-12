<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\Base\Event;
use Morpho\App\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\DispatchErrorHandler;
use Morpho\App\Web\EventManager;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\View;

class EventManagerTest extends TestCase {
    public function testDispatchErrorHandler() {
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
        $dispatchErrorHandler->expects($this->exactly(\count($handlers)))
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
            ->method('offsetGet')
            ->with($this->identicalTo('dispatchErrorHandler'))
            ->will($this->returnValue($dispatchErrorHandler));
        /** @noinspection PhpParamsInspection */
        $eventManager = new EventManager($serviceManager);
        $event = new Event('dispatchError', ['exception' => $exception, 'request' => $request]);

        $eventManager->trigger($event);
    }

    public function testAfterDispatchHandler_CallsActionResultRenderer() {
        $request = $this->mkRequest(true, false, $this->createMock(View::class));
        $serviceManager = $this->createMock(ServiceManager::class);
        $actionResultRenderer = new class {
            public $args;
            public function __invoke(...$args) {
                return $this->args = $args;
            }
        };
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->with('actionResultRenderer')
            ->willReturn($actionResultRenderer);
        /** @noinspection PhpParamsInspection */
        $eventManager = new EventManager($serviceManager);
        $event = new Event('afterDispatch', ['request' => $request]);

        $eventManager->trigger($event);

        $this->assertSame([$request], $actionResultRenderer->args);
    }

    private function mkRequest(bool $isRedirect, $result) {
        $request = new Request();
        $response = $this->mkResponse($isRedirect);
        $response['result'] = $result;
        $request->setResponse($response);
        return $request;
    }

    private function mkResponse(bool $isRedirect) {
        $response = new class ($isRedirect) extends Response {
            private $isRedirect;

            public function __construct(bool $isRedirect) {
                parent::__construct([]);
                $this->isRedirect = $isRedirect;
            }

            public function isRedirect(): bool {
                return $this->isRedirect;
            }
        };
        return $response;
    }
}
