<?php
namespace Morpho\Cli;

use Morpho\Core\Router as BaseRouter;

class Router extends BaseRouter {
    public function route($request) {
        $request->setModuleName('console')
            ->setControllerName('command')
            ->setActionName('index');
    }
}
