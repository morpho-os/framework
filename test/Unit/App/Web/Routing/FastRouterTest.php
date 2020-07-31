<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Routing;

use Morpho\App\IRequest;
use Morpho\App\IResponse;
use Morpho\App\Web\Routing\FastRouter;
use Morpho\Caching\ICache;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use FastRoute\Dispatcher as IDispatcher;

class FastRouterTest extends TestCase {
    public function dataForRoute() {
        // valid HTTP method and path
        yield [
            'GET',
            '/foo/bar',
            [
                IDispatcher::FOUND,
                ['this is found handler'],
                ['my args'],
            ],
            [
                'this is found handler',
                'args' => ['my args'],
            ],
        ];
        // valid HTTP method, invalid path
        yield [
            'GET',
            '/foo',
            [
                IDispatcher::NOT_FOUND,
                null,
            ],
            [
                'this is notFound handler',
            ],
        ];
        // invalid HTTP method, valid path
        yield [
            'PATCH',
            '/foo/bar',
            [
                IDispatcher::METHOD_NOT_ALLOWED,
                null
            ],
            [
                'this is methodNotAllowed handler',
            ],
        ];
    }

    /**
     * @dataProvider dataForRoute
     */
    public function testRoute(string $httpMethod, string $requestPath, array $routeInfo, array $expectedHandler) {
        $uri = new class ($requestPath) {
            private string $path;

            public function __construct(string $path) {
                $this->path = $path;
            }

            public function path() {
                return new class ($this->path) {
                    private string $path;

                    public function __construct(string $path) {
                        $this->path = $path;
                    }

                    public function toStr(): string {
                        return $this->path;
                    }
                };
            }
        };
        $request = new class ($httpMethod, $uri) extends \ArrayObject implements IRequest {
            private array $handler = [];
            private string $httpMethod;
            private $uri;

            public function __construct(string $httpMethod, $uri) {
                $this->httpMethod = $httpMethod;
                $this->uri = $uri;
            }

            public function setHandler(array $handler): void {
                $this->handler = $handler;
            }

            public function handler(): array {
                return $this->handler;
            }

            public function uri() {
                return $this->uri;
            }

            public function method(): string {
                return 'GET';
            }

            public function isHandled(bool $flag = null): bool {
            }

            public function setResponse(IResponse $response): void {
            }

            public function response(): IResponse {
            }

            public function args($namesOrIndexes = null) {
            }

            public function arg($nameOrIndex) {
            }
        };
        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('conf')
            ->willReturn([
                'router' => [
                    'handlers' => [
                        'badRequest' => ['this is badRequest handler'],
                        'notFound' => ['this is notFound handler'],
                        'methodNotAllowed' => ['this is methodNotAllowed handler'],
                    ],
                ],
            ]);
        $cache = new class implements ICache {
            public function get($key, $default = null) {
            }

            public function set($key, $value, $ttl = null) {
            }

            public function delete($key) {
            }

            public function clear() {
            }

            public function getMultiple($keys, $default = null) {
            }

            public function setMultiple($values, $ttl = null) {
            }

            public function deleteMultiple($keys) {
            }

            public function has($key) {
            }

            public function stats(): ?array {
            }
        };
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->with($this->equalTo('routerCache'))
            ->willReturn($cache);
        $router = new class ($routeInfo) extends FastRouter {
            private $routeInfo;

            public function __construct($routeInfo) {
                $this->routeInfo = $routeInfo;
            }

            protected function mkDispatcher(): IDispatcher {
                return new class ($this->routeInfo) implements IDispatcher {
                    private $routeInfo;

                    public function __construct($routeInfo) {
                        $this->routeInfo = $routeInfo;
                    }

                    public function dispatch(string $httpMethod, string $uri): array {
                        return $this->routeInfo;
                    }
                };
            }
        };
        $router->setServiceManager($serviceManager);

        $this->assertSame([], $request->handler());

        $router->route($request);

        $this->assertSame($expectedHandler, $request->handler());
    }
}
