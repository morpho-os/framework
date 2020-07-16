<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use Morpho\Base\IFn;
use Morpho\App\Web\Request;
use Morpho\Fs\Path;
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
        if (false !== \strpos($docComment, '@@')) {
            $httpMethodsRegexpPart = '(?:' . \implode('|', Request::knownMethods()) . ')';
            $routeRegExp = '~'
                . '@@(?<httpMethod>' . $httpMethodsRegexpPart . '(?:\|' . $httpMethodsRegexpPart . ')?)    # method (required)
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

            if (\preg_match('~^\s*\*\s*@@Title\s+(.+)\s*$~m', $docComment, $match)) {
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
        $i = 0;
        $routesMeta = [array_merge($actionMeta, $this->routeMeta($actionMeta))];
        if (!empty($actionMeta['docComment'])) {
            $docComment = self::parseDocComment($actionMeta['docComment']);
            ['title' => $title, 'uri' => $uri, 'httpMethods' => $httpMethods] = $docComment;
            if ($title) {
                $routesMeta[$i]['title'] = $title;
            }
            if ($uri) {
                $routesMeta[$i]['uri'] = $uri;
            }
            if ($httpMethods) {
                foreach ((array)$httpMethods as $httpMethod) {
                    if ($i > 0) {
                        $routesMeta[$i] = $routesMeta[$i - 1];
                    }
                    $routesMeta[$i]['httpMethod'] = $httpMethod;
                    $i++;
                }
                return $routesMeta;
            }
        }
        return $routesMeta;
    }

    protected function routeMeta(array $actionMeta): array {
        $modulePath = dasherize(last($actionMeta['module'], '/'), '.');

        $basePath = '/';

        if (!\preg_match('~(?P<controllerNs>.*?\\\\(?:Web|Cli))\\\\(?P<controller>.*?)$~s', $actionMeta['class'], $match) || !endsWith($match['controller'], CONTROLLER_SUFFIX)) {
            throw new \UnexpectedValueException();
        }
        $controller = \substr($match['controller'], 0, -\strlen(CONTROLLER_SUFFIX));
        $controllerPath = \str_replace('\\', '/', dasherize($controller, '\\'));

        $method = $actionMeta['method'];
        if (isset($this->restActions[$method])) {
            $actionPath = $this->restActions[$method][1];
            $httpMethod = $this->restActions[$method][0];
        } else {
            $actionPath = dasherize($method);
            $httpMethod = 'GET';
        }

        $uri = Path::combine($basePath, $modulePath, $controllerPath, $actionPath);

        return [
            'httpMethod' => $httpMethod,
            'uri' => $uri,
            'modulePath' => $modulePath,
            'controllerPath' => $controllerPath,
            'actionPath' => $actionPath,
        ];
    }
}
