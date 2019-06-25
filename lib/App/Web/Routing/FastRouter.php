<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use FastRoute\Dispatcher as IDispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use Morpho\App\Web\Uri;
use function Morpho\Base\compose;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\RouteParser\Std as StdRouteParser;
use Morpho\App\IRouter;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Fs\File;
use Morpho\App\Web\Request;

class FastRouter implements IHasServiceManager, IRouter {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    protected $homePath = '/';

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function route($request): void {
        /** @var Request $request */
        $uri = $request->uri();

        if ($this->handleHome($request, $uri)) {
            return;
        }

        $routeInfo = $this->dispatcher()
            ->dispatch($request->method(), $uri->path()->toStr(false));
        switch ($routeInfo[0]) {
            case -1:
                $handler = $this->config()['handlers']['badRequest'];
                $request->setHandler($handler);
                break;
            case IDispatcher::NOT_FOUND:
                $handler = $this->config()['handlers']['notFound'];
                $request->setHandler($handler);
                break;
            case IDispatcher::METHOD_NOT_ALLOWED:
                $handler = $this->config()['handlers']['methodNotAllowed'];
                $request->setHandler($handler);
                break;
            case IDispatcher::FOUND:
                $handlerInfo = $routeInfo[1];
                $request->setModuleName($handlerInfo['module']);
                $request->setControllerName($handlerInfo['controller']);
                $request->setActionName($handlerInfo['action']);
                $params = $routeInfo[2] ?? null;
                if ($params) {
                    $request['routing'] = $params;
                }
                break;
            default:
                throw new \UnexpectedValueException();
        }
    }

    public function rebuildRoutes(): void {
        $cacheFilePath = $this->cacheFilePath();
        $routeCollector = new RouteCollector(new StdRouteParser(), new GroupCountBasedDataGenerator());
        foreach ($this->routesMeta() as $routeMeta) {
            $routeMeta['uri'] = \preg_replace_callback('~\$[a-z_][a-z_0-9]*~si', function ($matches) {
                $var = \array_pop($matches);
                return '{' . \str_replace('$', '', $var) . ':[^/]+}';
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

    protected function handleHome(Request $request, Uri\Uri $uri): bool {
        if ($uri->path()->toStr(false) === $this->homePath) {
            $routerConfig = $this->config();
            $request->setHandler($routerConfig['handlers']['home']);
            $request->setMethod(\Zend\Http\Request::METHOD_GET);
            return true;
        }
        return false;
    }

    protected function routesMeta(): iterable {
        /** @var \Morpho\App\ModuleIndex $moduleIndex */
        $moduleIndex = $this->serviceManager['moduleIndex'];
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
        return \is_file($this->cacheFilePath());
    }

    protected function loadDispatchData() {
        return require $this->cacheFilePath();
    }

    protected function config(): array {
        return $this->serviceManager->config()['router'];
    }

    private function cacheFilePath(): string {
        $serviceManager = $this->serviceManager;
        $siteModuleName = $serviceManager['site']->moduleName();
        $cacheDirPath = $serviceManager['moduleIndex']->module($siteModuleName)->cacheDirPath();
        return $cacheDirPath . '/route.php';
    }
}
