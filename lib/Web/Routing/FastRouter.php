<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use function Morpho\Base\requireFile;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Web\ModuleManager;
use Morpho\Web\Request;

class FastRouter extends Router {
    public function route($request): void {
        $uri = $this->normalizedUri($request);
        if ($this->handleHomeUri($request, $uri)) {
            return;
        }

        $cacheFilePath = $this->cacheFilePath();
        if (!file_exists($cacheFilePath)) {
            $this->rebuildRoutes();
        }
        $dispatchData = requireFile($cacheFilePath);
        $dispatcher = new GroupCountBasedDispatcher($dispatchData);

        $routeInfo = $dispatcher->dispatch($request->method(), $uri);
        if ($routeInfo[0] === Dispatcher::FOUND) {
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

    protected function cacheFilePath(): string {
        return $this->serviceManager->get('site')->fs()->cacheDirPath() . '/route.php';
    }

    protected function normalizedUri($request): string {
        $uri = Path::normalize($request->uriPath());
        return $uri === '' ? '/' : $uri;
    }

    protected function handleHomeUri(Request $request, $uri): bool {
        if ($uri === '/') {
            $settingsManager = $this->serviceManager->get('settingsManager');
            $handler = $settingsManager->get(Request::HOME_HANDLER, ModuleManager::SYSTEM_MODULE);
            if (false !== $handler) {
                $request->setHandler($handler['handler'])
                    ->setMethod(Request::GET_METHOD);
                return true;
            }
        }
        return false;
    }
}