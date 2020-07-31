<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use Morpho\Base\Arr;
use Morpho\Caching\ICache;
use function Morpho\Base\compose;
use FastRoute\Dispatcher as IDispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteParser\Std as StdRouteParser;
use Morpho\App\IRouter;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use function Morpho\Caching\cacheKey;

class FastRouter implements IHasServiceManager, IRouter {
    protected IServiceManager $serviceManager;

    protected ICache $cache;

    protected string $cacheKey;

    public function __construct() {
        $this->cacheKey = cacheKey($this, __FUNCTION__);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
        $this->cache = $this->serviceManager['routerCache'];
    }

    public function route($request): void {
        $routeInfo = $this->mkDispatcher()
            ->dispatch($request->method(), $request->uri()->path()->toStr(false));
        switch ($routeInfo[0]) {
            case IDispatcher::NOT_FOUND: // 404 Not Found
                $handler = $this->conf()['handlers']['notFound'];
                $request->setHandler($handler);
                break;
            case IDispatcher::METHOD_NOT_ALLOWED: // 405 Method Not Allowed
                $handler = $this->conf()['handlers']['methodNotAllowed'];
                $request->setHandler($handler);
                break;
            case IDispatcher::FOUND: // 200 OK
                $handlerMeta = $routeInfo[1];
                $request->setHandler(array_merge($handlerMeta, ['args' => $routeInfo[2] ?? []]));
                break;
            default:
                throw new \UnexpectedValueException();
        }
    }

    public function rebuildRoutes(): void {
        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());
        foreach ($this->routesMeta() as $routeMeta) {
            $routeMeta['uri'] = \preg_replace_callback('~\$[a-z_][a-z_0-9]*~si', function ($matches) {
                $var = \array_pop($matches);
                return '{' . \str_replace('$', '', $var) . ':[^/]+}';
            }, $routeMeta['uri']);
            $routeCollector->addRoute($routeMeta['httpMethod'], $routeMeta['uri'], Arr::only($routeMeta, ['module', 'class', 'method', 'modulePath', 'controllerPath']));
        }
        $dispatchData = $routeCollector->getData();
        $this->cache->set($this->cacheKey, $dispatchData);
    }

    protected function mkDispatcher(): IDispatcher {
        if (!$this->cache->has($this->cacheKey)) {
            $this->rebuildRoutes();
        }
        $dispatchData = $this->cache->get($this->cacheKey);
        return new GroupCountBasedDispatcher($dispatchData);
    }

    protected function routesMeta(): iterable {
        $moduleIndex = $this->serviceManager['serverModuleIndex'];
        $modules = function () use ($moduleIndex) {
            foreach ($moduleIndex as $moduleName) {
                yield $moduleIndex->module($moduleName);
            }
        };
        $actionMetaProvider = new ActionMetaProvider();
        return compose(
            $this->serviceManager['routeMetaProvider'],
            $actionMetaProvider,
        )($modules);
    }

    protected function conf(): array {
        return $this->serviceManager->conf()['router'];
    }
}
