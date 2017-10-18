<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use FastRoute\Dispatcher as IDispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use function Morpho\Base\compose;
use function Morpho\Base\requireFile;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteParser\Std as StdRouteParser;
use Morpho\Core\IRouter;
use Morpho\Di\IHasServiceManager;
use Morpho\Di\IServiceManager;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Web\Request;

class FastRouter implements IHasServiceManager, IRouter {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function route($request): void {
        $uri = Path::normalize($request->uriPath());
        if ($uri === '') {
            $uri = '/';
        }

        if ($this->handleHomeUri($request, $uri)) {
            return;
        }

        $routeInfo = $this->dispatcher()
            ->dispatch($request->method(), $uri);
        if ($routeInfo[0] === IDispatcher::FOUND) {
            $handlerInfo = $routeInfo[1];
            $request->setModuleName($handlerInfo['module'])
                ->setControllerName($handlerInfo['controller'])
                ->setActionName($handlerInfo['action']);
            $params = $routeInfo[2] ?? null;
            if ($params) {
                $request->setRoutingParams($params);
            }
        }
    }

    public function rebuildRoutes(): void {
        $cacheFilePath = $this->cacheFilePath();
        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());
        foreach ($this->routesMeta() as $routeMeta) {
            $routeMeta['uri'] = preg_replace_callback('~\$[a-z_][a-z_0-9]*~si', function ($matches) {
                $var = array_pop($matches);
                return '{' . str_replace('$', '', $var) . ':[^/]+}';
            }, $routeMeta['uri']);
            $handler = [
                'module'     => $routeMeta['module'],
                'controller' => $routeMeta['controller'],
                'action'     => $routeMeta['action'],
            ];
            $routeCollector->addRoute($routeMeta['httpMethod'], $routeMeta['uri'], $handler);
        }
        $dispatchData = $routeCollector->getData();
        File::writePhpVar($cacheFilePath, $dispatchData, false);
    }

    protected function dispatcher(): IDispatcher {
        $cacheFilePath = $this->cacheFilePath();
        if (!file_exists($cacheFilePath)) {
            $this->rebuildRoutes($cacheFilePath);
        }
        $dispatchData = requireFile($cacheFilePath);
        return new GroupCountBasedDispatcher($dispatchData);
    }

    protected function cacheFilePath(): string {
        return $this->serviceManager->get('site')->pathManager()->cacheDirPath() . '/route.php';
    }

    protected function handleHomeUri(Request $request, $uri): bool {
        if ($uri === '/') {
            $routerConfig = $this->serviceManager->config()['router'];
            if (isset($routerConfig['home'])) {
                $handler = $routerConfig['home'];
                $request->setHandler($handler['handler'])
                    ->setMethod($handler['method']);
                return true;
            }
        }
        return false;
    }

    protected function routesMeta(): iterable {
        $modules = $this->serviceManager->get('site')->config()['modules'];
        return compose(
            new RouteMetaProvider(),
            compose(
                new ActionMetaProvider(),
                new ControllerFileMetaProvider($this->serviceManager->get('moduleProvider'))
            )
        )($modules);
    }
}