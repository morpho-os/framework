<?php
namespace Morpho\Web\Routing;

class FallbackRouter {
    public function route($request) {
        $path = rtrim($request->uri()->getPath(), '/');
        $parts = array_slice(array_filter(explode('/', $path)), 0, 9);
        $routes = [
            //'GET'  => ['check-env'],
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
        $request->setModuleName(SYSTEM_MODULE)
            ->setControllerName('Install')
            ->setActionName($action);
    }
}
