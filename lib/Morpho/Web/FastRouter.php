<?php
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Code\CodeTool;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;

class FastRouter extends BaseRouter {
    public function route($request) {
        $uri = $this->getNormalizedUri($request);
        if ($this->handleHomeUri($request, $uri)) {
            return;
        }

        $cacheFilePath = $this->getCacheFilePath();
        if (!file_exists($cacheFilePath)) {
            $this->rebuildRoutes(MODULE_DIR_PATH, $cacheFilePath);
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

    public function rebuildRoutes(...$args) {
        $routesMeta = $this->buildRoutesMeta($args[0]);
        $cacheFilePath = isset($args[1]) ? $args[1] : $this->getCacheFilePath();

        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());

        foreach ($routesMeta as $routeMeta) {
            foreach ($routeMeta['controllers'] as $controllerMeta) {
                foreach ($controllerMeta['actions'] as $actionMeta) {
                    if (preg_match('~[^\w/-]~si', $actionMeta['uri'])) {
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
        CodeTool::varToPhp($dispatchData, $cacheFilePath);
    }

    private function getCacheFilePath() {
        return $this->serviceManager->get('pathManager')->getCacheDirPath() . '/routes.php';
    }

    public function assemble($action, $httpMethod, $controller, $module, $params) {
        throw new NotImplementedException();
    }
}