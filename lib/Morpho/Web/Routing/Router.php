<?php
namespace Morpho\Web\Routing;

use Morpho\Di\{
    IServiceManager, IServiceManagerAware
};
use function Morpho\Base\{
    classify, dasherize, head
};
use Morpho\Fs\Path;
use Morpho\Web\Request;

/**
 * This class applies some ideas found at:
 *     * the Drupal-8 routing system (http://drupal.org) (@TODO: Not actual anymore?)
 *     * Rails 4.x Routing, @see http://guides.rubyonrails.org/routing.html
 */
abstract class Router implements IServiceManagerAware {
    const MAX_PARTS_COUNT = 9;

    protected $serviceManager;

    protected $moduleDirPath;

    protected $restActions = [
        'index'  => ['GET', null],       // GET    /$module/$entityType
        'list'   => ['GET', 'list'],     // GET    /$module/$entityType/list
        'new'    => ['GET', 'new'],      // GET    /$module/$entityType/new
        'create' => ['POST', null],      // POST   /$module/$entityType
        'show'   => ['GET', '$id'],      // GET    /$module/$entityType/$entityId
        'edit'   => ['GET', '$id/edit'], // GET    /$module/$entityType/$entityId/edit
        'update' => ['PATCH', '$id'],    // PATCH  /$module/$entityType/$entityId
        'delete' => ['DELETE', '$id'],   // DELETE /$module/$entityType/$entityId
    ];

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function setModuleDirPath(string $moduleDirPath) {
        $this->moduleDirPath = $moduleDirPath;
    }

    public function getModuleDirPath() {
        if (null === $this->moduleDirPath) {
            $this->moduleDirPath = MODULE_DIR_PATH;
        }
        return $this->moduleDirPath;
    }

    abstract public function route($request);

    abstract public function rebuildRoutes();

    abstract public function assemble(string $action, string $httpMethod, string $controller, string $module, array $params = null);

    public function dumpRoutes(): array {
        return iterator_to_array($this->buildRoutesMeta(MODULE_DIR_PATH));
    }

    protected function buildRoutesMeta(string $moduleDirPath): \Generator {
        $filePaths = RouteInfoProvider::enumerateControllerFiles($moduleDirPath);
        foreach ($filePaths as $filePath) {
            $module = classify(
                head(
                    Path::toRelative($moduleDirPath, $filePath),
                    '/'
                )
            );
            $controllersInFileMeta = RouteInfoProvider::getMetaForControllersInFile($filePath);
            foreach ($controllersInFileMeta as $controllerMeta) {
                if (!empty($controllerMeta['actions'])) {
                    foreach ($this->normalizeControllerActionMeta($controllerMeta['actions'], $module, $controllerMeta['controller']) as $actionMeta) {
                        yield [
                            'httpMethod' => $actionMeta['httpMethod'],
                            'uri'        => $actionMeta['uri'],
                            'module'     => $module,
                            'controller' => $controllerMeta['controller'],
                            'action'     => $actionMeta['action'],
                            'filePath'   => $filePath,
                            'class'      => $controllerMeta['class'],
                            'title'      => $actionMeta['title'],
                        ];
                    }
                }
            }
        }
    }

    protected function handleHomeUri(Request $request, $uri) {
        if ($uri === '/') {
            $mca = $this->serviceManager
                ->get('settingManager')
                ->get('homeMCA', 'system');
            if (false !== $mca) {
                $request->setModuleName($mca['module'])
                    ->setControllerName($mca['controller'])
                    ->setActionName($mca['action'])
                    ->setMethod(Request::GET_METHOD);
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $uri
     * @return array
     */
    protected function splitUri($uri) {
        $uriParts = array_slice(array_filter(explode('/', $uri), function ($value) {
            return $value !== null && $value !== '';
        }), 0, self::MAX_PARTS_COUNT);
        return $uriParts;
    }

    protected function getNormalizedUri($request) {
        $uri = Path::normalize($request->getUriPath());
        return $uri === '' ? '/' : $uri;
    }

    private function normalizeControllerActionMeta(array $controllerActionMeta, $module, $controller) {
        foreach ($controllerActionMeta as $actionMeta) {
            $action = $actionMeta['action'];
            $title = null;

            $uri = '/' . dasherize($module) . '/' . dasherize($controller);
            if (isset($this->restActions[$action])) {
                $uri .= rtrim('/' . $this->restActions[$action][1], '/');
                $httpMethod = $this->restActions[$action][0];
            } else {
                $httpMethod = Request::GET_METHOD;
                $uri .= '/' . dasherize($action);
            }

            $yield = true;
            if (!empty($actionMeta['docComment'])) {
                $docComment = RouteInfoProvider::parseDocComment($actionMeta['docComment']);
                if ($docComment['title']) {
                    $title = $docComment['title'];
                }
                if ($docComment['uri']) {
                    $uri = $docComment['uri'];
                }
                if (!empty($docComment['methods'])) {
                    $yield = false;
                    foreach ((array)$docComment['methods'] as $httpMethod) {
                        $actionMeta = [
                            'title'      => $title,
                            'action'     => $action,
                            'httpMethod' => $httpMethod,
                            'uri'        => $uri,
                        ];
                        yield $actionMeta;
                    }
                }
            }
            unset($actionMeta['docComment']);

            if ($yield) {
                $actionMeta = [
                    'title'      => $title,
                    'action'     => $action,
                    'httpMethod' => $httpMethod,
                    'uri'        => $uri,
                ];
                yield $actionMeta;
            }
        }
    }
}