<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use Morpho\Base\IFn;
use Morpho\App\Web\Request;
use function Morpho\Base\{dasherize, endsWith, last};
use const Morpho\App\CONTROLLER_SUFFIX;

class RouteMetaProvider implements IFn {
    protected array $restActions = [
        'index'  => ['GET', null],       // GET    $entityType
        'list'   => ['GET', 'list'],     // GET    $entityType/list
        'new'    => ['GET', 'new'],      // GET    $entityType/new
        'create' => ['POST', null],      // POST   $entityType
        'show'   => ['GET', '$id'],      // GET    $entityType/$entityId
        'edit'   => ['GET', '$id/edit'], // GET    $entityType/$entityId/edit
        'update' => ['PATCH', '$id'],    // PATCH  $entityType/$entityId
        'delete' => ['DELETE', '$id'],   // DELETE $entityType/$entityId
    ];

    public function __invoke($actionMetas): iterable {
        foreach ($actionMetas as $actionMeta) {
            // 1 $actionMeta can be mapped to the >= 1 $routeMeta
            yield from $this->actionMetaToRoutesMeta($actionMeta);
        }
    }

    public static function parseDocComment(string $docComment): array {
        $httpMethods = $title = $uri = null;
        if (false !== \strpos($docComment, '@')) {
            $httpMethodsRegexpPart = '(?:' . \implode('|', Request::knownMethods()) . ')';
            $routeRegExp = '~'
                . '@(?<httpMethod>' . $httpMethodsRegexpPart . '(?:\|' . $httpMethodsRegexpPart . ')?)    # method (required)
                (\s+(?<uri>([^*\s]+)))?                                                                   # uri    (optional)
                ~xm';
            if (\preg_match($routeRegExp, $docComment, $match)) {
                $httpMethods = \explode('|', $match['httpMethod']);
                if (!empty($match['uri'])) {
                    $uri = $match['uri'];
                    if ($uri[0] !== '/') {
                        throw new \RuntimeException("Invalid annotations, URI must start with slash (/)");
                    }
                }
            }

            if (\preg_match('~^\s*\*\s*@Title\s+(.+)\s*$~m', $docComment, $match)) {
                $title = \array_pop($match);
            }
        }
        return [
            'httpMethods' => $httpMethods,
            'uri'         => $uri,
            'title'       => $title,
        ];
    }

    protected function actionMetaToRoutesMeta(array $actionMeta): array {
        $routesMeta = [];
        $i = 0;

        $routesMeta[$i] = $this->actionMetaToRouteMeta($actionMeta);

        if (!empty($actionMeta['docComment'])) {
            $docComment = self::parseDocComment($actionMeta['docComment']);
            if ($docComment['title']) {
                $routesMeta[$i]['title'] = $docComment['title'];
            }
            if ($docComment['uri']) {
                $routesMeta[$i]['uri'] = $docComment['uri'];
            }
            if (!empty($docComment['httpMethods'])) {
                foreach ((array)$docComment['httpMethods'] as $httpMethod) {
                    if ($i > 0) {
                        // ?
                        $routesMeta[$i] = $routesMeta[$i - 1];
                    }
                    $routesMeta[$i]['httpMethod'] = $httpMethod;
                    $i++;
                }
            }
        }

        return $routesMeta;
    }

    protected function actionMetaToRouteMeta(array $actionMeta): array {
        $shortModuleName = dasherize(last($actionMeta['module'], '/'), '.');

        $actionMeta['shortModule'] = $shortModuleName;

        $baseUri = '/';

        if (!\preg_match('~(?P<controllerNs>.*?\\\\(?:Web|Cli))\\\\(?P<controller>.*?)$~s', $actionMeta['class'], $match) || !endsWith($match['controller'], CONTROLLER_SUFFIX)) {
            throw new \UnexpectedValueException();
        }
        $controller = \substr($match['controller'], 0, -\strlen(CONTROLLER_SUFFIX));
        $controllerPath = \str_replace('\\', '/', dasherize($controller, '\\'));
        $actionMeta['controllerPath'] = $controllerPath;

        $uri = \rtrim($baseUri, '/') . '/' . $shortModuleName . '/' . $controllerPath;

        $action = $actionMeta['action'];
        if (isset($this->restActions[$action])) {
            $uri .= \rtrim('/' . $this->restActions[$action][1], '/');
            $httpMethod = $this->restActions[$action][0];
        } else {
            $httpMethod = 'GET';
            $uri .= '/' . dasherize($action);
        }

        $actionMeta['httpMethod'] = $httpMethod;
        $actionMeta['uri'] = $uri;

        return $actionMeta;
    }
}
