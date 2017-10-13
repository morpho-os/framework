<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

abstract class Router {
    public function route(Request $request): void {
        [$moduleName, $controllerName, $actionName] = $this->parseArgs($request->args());
        $request->setModuleName($moduleName)
            ->setControllerName($controllerName)
            ->setActionName($actionName);
    }

    abstract protected function parseArgs(array $args): array;
}