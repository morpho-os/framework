<?php declare(strict_types=1);
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
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteParser\Std as StdRouteParser;
use Morpho\Core\IRouter;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Web\Request;

class FastRouter implements IHasServiceManager, IRouter {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function route($request): void {
        /** @var Request $request */
        $uri = Path::normalize($request->uri()->path()->toStr(false));
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
            $request->setModuleName($handlerInfo['module']);
            $request->setControllerName($handlerInfo['controller']);
            $request->setActionName($handlerInfo['action']);
            $params = $routeInfo[2] ?? null;
            if ($params) {
                $request['routing'] = $params;
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
        if (!$this->cacheExists()) {
            $this->rebuildRoutes();
        }
        $dispatchData = $this->loadDispatchData();
        return new GroupCountBasedDispatcher($dispatchData);
    }

    protected function handleHomeUri(Request $request, $uri): bool {
        if ($uri === '/') {
            $routerConfig = $this->serviceManager->config()['router'];
            if (isset($routerConfig['home'])) {
                $handler = $routerConfig['home'];
                $request->setHandler($handler['handler']);
                $request->setMethod($handler['method']);
                return true;
            }
        }
        return false;
    }

    protected function routesMeta(): iterable {
        $moduleIndex = $this->serviceManager->get('moduleIndex');
        $modules = $moduleIndex->moduleNames();
        return compose(
            new RouteMetaProvider(),
            compose(
                new ActionMetaProvider(),
                new ControllerFileMetaProvider($moduleIndex)
            )
        )($modules);
    }

    protected function cacheExists(): bool {
        return is_file($this->cacheFilePath());
    }

    protected function loadDispatchData() {
        return require $this->cacheFilePath();
    }

    private function cacheFilePath(): string {
        $serviceManager = $this->serviceManager;
        $siteModuleName = $serviceManager->get('site')->moduleName();
        $cacheDirPath = $serviceManager->get('moduleIndex')->moduleMeta($siteModuleName)->cacheDirPath();
        return $cacheDirPath . '/route.php';
    }
}
