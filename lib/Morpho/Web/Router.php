<?php
namespace Morpho\Web;

use Morpho\Di\{IServiceManager, IServiceManagerAware};
use function Morpho\Base\{classify, dasherize, head};
use Morpho\Fs\Path;

/**
 * This class applies some ideas found at:
 *     * the Drupal-8 routing system (http://drupal.org)
 *     * Rails 4.x Routing, @see http://guides.rubyonrails.org/routing.html
 */
abstract class Router implements IServiceManagerAware {
    protected $serviceManager;

    const MAX_PARTS_COUNT = 9;

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

    abstract public function route($request);

    abstract public function rebuildRoutes(...$args);

    abstract public function assemble($action, $httpMethod, $controller, $module, $params);

    protected function buildRoutesMeta(...$args) {
        if (count($args) !== 1) {
            throw new \LogicException("Invalid number of arguments");
        }
        $moduleDirPath = $args[0];
        $routesMeta = [];
        $filePaths = RouteInfoProvider::enumerateControllerFiles($moduleDirPath);
        $i = 0;
        foreach ($filePaths as $filePath) {
            $routesMeta[$i]['filePath'] = $filePath;
            $routesMeta[$i]['module'] = $module = classify(
                head(
                    Path::toRelative($moduleDirPath, $filePath),
                    '/'
                )
            );
            $controllersInFileMeta = RouteInfoProvider::buildMetaForControllersInFile($filePath);
            $routesMeta[$i]['controllers'] = [];
            foreach ($controllersInFileMeta as $controllerMeta) {
                $controllerMeta['actions'] = !empty($controllerMeta['actions'])
                    ? iterator_to_array(
                        $this->normalizeControllerActionMeta(
                            $controllerMeta['actions'],
                            $module,
                            $controllerMeta['controller']
                        )
                    )
                    : [];
                $routesMeta[$i]['controllers'][] = $controllerMeta;
            }
            $i++;
        }
        return $routesMeta;
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
        $uri = Path::normalize($request->getUri()->getPath());
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
                            'title' => $title,
                            'action' => $action,
                            'httpMethod' => $httpMethod,
                            'uri' => $uri,
                        ];
                        yield $actionMeta;
                    }
                }
            }
            unset($actionMeta['docComment']);

            if ($yield) {
                $actionMeta = [
                    'title' => $title,
                    'action' => $action,
                    'httpMethod' => $httpMethod,
                    'uri' => $uri,
                ];
                yield $actionMeta;
            }
        }
    }
}