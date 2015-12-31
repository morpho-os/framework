<?php
namespace Morpho\Web\Routing;

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
        $cacheFilePath = $this->getCacheFilePath();
        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());
        foreach ($this->buildRoutesMeta($this->getModuleDirPath()) as $routeMeta) {
            $routeMeta['uri'] = preg_replace_callback('~\$[a-z_][a-z_0-9]*~si', function ($matches) {
                return '{' . str_replace('$', '', array_pop($matches)) . '}';
            }, $routeMeta['uri']);
            $handler = [
                'module'     => $routeMeta['module'],
                'controller' => $routeMeta['controller'],
                'action'     => $routeMeta['action'],
            ];
            $routeCollector->addRoute($routeMeta['httpMethod'], $routeMeta['uri'], $handler);
        }
        $dispatchData = $routeCollector->getData();
        CodeTool::writeVarToFile($dispatchData, $cacheFilePath);
    }

    public function assemble(string $action, string $httpMethod, string $controller, string $module, array $params = null) {
        throw new NotImplementedException();
    }

    protected function getCacheFilePath() {
        return $this->serviceManager->get('siteManager')->getCurrentSite()->getCacheDirPath() . '/routes.php';
    }
}