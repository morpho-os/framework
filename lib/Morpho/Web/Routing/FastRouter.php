<?php
namespace Morpho\Web\Routing;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Web\Request;

class FastRouter extends Router {
    public function route($request)/*: void*/ {
        $uri = $this->getNormalizedUri($request);
        if ($this->handleHomeUri($request, $uri)) {
            return;
        }

        $cacheFilePath = $this->getCacheFilePath();
        if (!file_exists($cacheFilePath)) {
            $this->rebuildRoutes();
        }
        $dispatchData = require $cacheFilePath;
        $dispatcher = new GroupCountBasedDispatcher($dispatchData);

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $uri);
        if ($routeInfo[0] === Dispatcher::FOUND) {
            $handlerInfo = $routeInfo[1];
            $request->setModuleName($handlerInfo['module'])
                ->setControllerName($handlerInfo['controller'])
                ->setActionName($handlerInfo['action']);
            $params = $routeInfo[2] ?? null;
            if ($params) {
                $request->setParams($params);
            }
        }
    }

    public function rebuildRoutes()/*: void*/ {
        $cacheFilePath = $this->getCacheFilePath();
        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());
        foreach ($this->getRoutesMeta() as $routeMeta) {
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
        File::writePhp($cacheFilePath, $dispatchData, false);
    }

    protected function getCacheFilePath(): string {
        return $this->serviceManager->get('siteManager')
            ->getCurrentSite()
            ->getCacheDirPath() . '/route.php';
    }

    protected function getNormalizedUri($request): string {
        $uri = Path::normalize($request->getUriPath());
        return $uri === '' ? '/' : $uri;
    }

    protected function handleHomeUri(Request $request, $uri): bool {
        if ($uri === '/') {
            $handler = $this->serviceManager
                ->get('settingManager')
                ->get('homeHandler', 'system');
            if (false !== $handler) {
                $request->setHandler($handler)
                    ->setMethod(Request::GET_METHOD);
                return true;
            }
        }
        return false;
    }
}