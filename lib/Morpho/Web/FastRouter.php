<?php
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Code\CodeTool;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;

class FastRouter extends Router {
    public function route($request) {
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
            $route = $routeInfo[1];

            $request->setModuleName($route['module'])
                ->setControllerName($route['controller'])
                ->setActionName($route['action']);
            /*
                ->setMethod($route['method'])
                ->setParams($route['params']);
            */
        }
    }

    public function rebuildRoutes() {
        $routesMeta = $this->buildRoutesMeta($this->getModuleDirPath());
        $cacheFilePath = $this->getCacheFilePath();

        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());

        foreach ($routesMeta as $routeMeta) {
            foreach ($routeMeta['controllers'] as $controllerMeta) {
                foreach ($controllerMeta['actions'] as $actionMeta) {
                    if (preg_match('~[^\w/-]~si', $actionMeta['uri'])) {
                        // @TODO
                        d($actionMeta);
                    }
                    $handler = [
                        'module' => $routeMeta['module'],
                        'controller' => $controllerMeta['controller'],
                        'action' => $actionMeta['action']
                    ];
                    $routeCollector->addRoute($actionMeta['httpMethod'], $actionMeta['uri'], $handler);
                }
            }
        }

        $dispatchData = $routeCollector->getData();
        CodeTool::writeVarToFile($dispatchData, $cacheFilePath);
    }

    protected function getCacheFilePath() {
        return $this->serviceManager->get('siteManager')->getCurrentSite()->getCacheDirPath() . '/routes.php';
    }

    public function assemble(string $action, string $httpMethod, string $controller, string $module, array $params = null) {
        throw new NotImplementedException();
    }
}