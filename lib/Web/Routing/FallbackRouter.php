<?php
namespace Morpho\Web\Routing;

use Morpho\Web\ModuleManager;

class FallbackRouter {
    public function route($request) {
        $path = rtrim($request->uri()->path(), '/');
        $parts = array_slice(array_filter(explode('/', $path)), 0, 9);
        $routes = [
            //'GET'  => ['check-env'],
            'POST' => ['install'],
        ];
        $httpMethod = $request->method();
        $action = 'index';
        if (isset($routes[$httpMethod])) {
            $allowedActions = $routes[$httpMethod];
            if (!empty($parts[0]) && in_array($parts[0], $allowedActions, true)) {
                $action = $parts[0];
            }
        }
        $request->setModuleName(ModuleManager::SYSTEM_MODULE)
            ->setControllerName('Install')
            ->setActionName($action);
    }
}
