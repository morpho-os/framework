<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web;

use Morpho\Base\Event;
use Morpho\Base\IFn;
use Morpho\Core\ServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\DispatchErrorHandler;
use Morpho\Web\EventManager;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\View\Page;

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
        /** @noinspection PhpParamsInspection */
        $eventManager = new EventManager($serviceManager);
        $event = new Event('dispatchError', ['exception' => $exception, 'request' => $request]);

        $eventManager->trigger($event);
    }

    public function testAfterDispatchHandler_CallsRenderer() {
        /** @noinspection PhpParamsInspection */
        $request = $this->newConfiguredRequest(true, false, $this->createMock(Page::class));
        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('get')
            ->with('contentNegotiator')
            ->willReturn(new class {
                public function __invoke() {
                    return 'html';
                }
            });

        /** @noinspection PhpParamsInspection */
        $eventManager = new class ($serviceManager) extends EventManager {
            public $renderer;
            protected function newRenderer(string $rendererType, IServiceManager $serviceManager): IFn {
                if ($rendererType === 'html') {
                    return $this->renderer;
                }
                throw new \UnexpectedValueException();
            }
        };
        $renderer = new class implements IFn {
            public $args;
            public function __invoke($value) {
                $this->args = func_get_args();
            }
        };
        $eventManager->renderer = $renderer;

        $event = new Event('afterDispatch', [
            'request' => $request,
        ]);

        $eventManager->trigger($event);

        $this->assertSame([$request], $renderer->args);
    }

    public function dataForShouldRender() {
        foreach ([true, false] as $isAjax) {
            yield [$isAjax, false, false, $this->createMock(Page::class), false];
            yield [$isAjax, false, false, null, false];
            yield [$isAjax, false, true, $this->createMock(Page::class), false];
            yield [$isAjax, false, true, null, false];
            yield [$isAjax, true, false, $this->createMock(Page::class), true];
            yield [$isAjax, true, false, null, false];
            yield [$isAjax, true, true, $this->createMock(Page::class), $isAjax];
            yield [$isAjax, true, true, null, false];
        }
    }

    /**
     * @dataProvider dataForShouldRender
     */
    public function testShouldRender(bool $isAjax, bool $isDispatched, bool $isRedirect, ?Page $page, bool $expected) {
        $request = $this->newConfiguredRequest($isDispatched, $isRedirect, $page);
        $request->isAjax($isAjax);
        $serviceManager = $this->createMock(IServiceManager::class);
        /** @noinspection PhpParamsInspection */
        $renderer = new EventManager($serviceManager);

        /** @noinspection PhpParamsInspection */
        $this->assertSame($expected, $renderer->shouldRender($request));
    }

    private function newConfiguredRequest(bool $isDispatched, bool $isRedirect, ?Page $page) {
        $request = new Request();
        $request->isDispatched($isDispatched);
        $response = new class ($isRedirect) extends Response {
            private $isRedirect;

            public function __construct(bool $isRedirect) {
                $this->isRedirect = $isRedirect;
            }

            public function isRedirect(): bool {
                return $this->isRedirect;
            }
        };
        if ($page) {
            $response['resource'] = $page;
        }
        $request->setResponse($response);
        return $request;
    }
}