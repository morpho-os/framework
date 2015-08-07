<?php
namespace Morpho\Web;

class FallbackRouter {
    public function route($request) {
        $path = rtrim($request->getUri()->getPath(), '/');
        $parts = array_slice(array_filter(explode('/', $path)), 0, 9);
        $routes = [
            'GET' => ['check-env'],
            'POST' => ['install'],
        ];
        $httpMethod = $request->getMethod();
        $action = 'index';
        if (isset($routes[$httpMethod])) {
            $allowedActions = $routes[$httpMethod];
            if (!empty($parts[0]) && in_array($parts[0], $allowedActions, true)) {
                $action = $parts[0];
            }
        }
        $request->setModuleName('System')
            ->setControllerName('Install')
            ->setActionName($action);
    }
}
