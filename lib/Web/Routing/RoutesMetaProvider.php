<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use function Morpho\Base\{
    dasherize, last
};
use Morpho\Web\Request;

class RoutesMetaProvider implements \IteratorAggregate {
    protected $restActions = [
        'index'  => ['GET', null],       // GET    $entityType
        'list'   => ['GET', 'list'],     // GET    $entityType/list
        'new'    => ['GET', 'new'],      // GET    $entityType/new
        'create' => ['POST', null],      // POST   $entityType
        'show'   => ['GET', '$id'],      // GET    $entityType/$entityId
        'edit'   => ['GET', '$id/edit'], // GET    $entityType/$entityId/edit
        'update' => ['PATCH', '$id'],    // PATCH  $entityType/$entityId
        'delete' => ['DELETE', '$id'],   // DELETE $entityType/$entityId
    ];

    protected $actionsMetaProvider;

    public function setActionsMetaProvider(\Traversable $actionsMetaProvider) {
        $this->actionsMetaProvider = $actionsMetaProvider;
    }

    public function actionsMetaProvider() {
        return $this->actionsMetaProvider;
    }

    public function getIterator(): \Generator {
        foreach ($this->actionsMetaProvider as $actionMeta) {
            // 1 $actionMeta can be mapped to the >= 1 $routeMeta
            foreach ($this->actionMetaToRoutesMeta($actionMeta) as $routeMeta) {
                yield $routeMeta;
            }
        }
    }

    public static function parseDocComment(string $docComment): array {
        $httpMethods = $title = $uri = null;
        if (false !== strpos($docComment, '@')) {
            $httpMethodsRegexpPart = '(?:' . implode('|', Request::methods()) . ')';
            $routeRegExp = '~'
                . '@(?<httpMethod>' . $httpMethodsRegexpPart . '(?:\|' . $httpMethodsRegexpPart . ')?)    # method (required)
                (\s+(?<uri>([^*\s]+)))?                                                                   # uri    (optional)
                ~xm';
            if (preg_match($routeRegExp, $docComment, $match)) {
                $httpMethods = explode('|', $match['httpMethod']);
                if (!empty($match['uri'])) {
                    $uri = $match['uri'];
                    if ($uri[0] !== '/') {
                        throw new \RuntimeException("Invalid annotations, URI must start with slash (/)");
                    }
                }
            }

            if (preg_match('~^\s*\*\s*@Title\s+(.+)\s*$~m', $docComment, $match)) {
                $title = array_pop($match);
            }
        }
        return [
            'httpMethods' => $httpMethods,
            'uri'         => $uri,
            'title'       => $title,
        ];
    }

    protected function actionMetaToRoutesMeta(array $actionMeta) {
        $routesMeta = [];
        $i = 0;

        $action = $actionMeta['action'];
        $title = null;

        $uri = '/' . dasherize(last($actionMeta['module'], '/'))
            . '/' . dasherize($actionMeta['controller']);
        if (isset($this->restActions[$action])) {
            $uri .= rtrim('/' . $this->restActions[$action][1], '/');
            $httpMethod = $this->restActions[$action][0];
        } else {
            $httpMethod = Request::GET_METHOD;
            $uri .= '/' . dasherize($action);
        }

        $routesMeta[$i] = [
            'httpMethod' => $httpMethod,
            'uri'        => $uri,
            'module'     => $actionMeta['module'],
            'controller' => $actionMeta['controller'],
            'action'     => $action,
            'title'      => $title,
            'class'      => $actionMeta['class'],
        ];

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
}