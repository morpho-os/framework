<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\Dispatcher as BaseDispatcher;
use Morpho\Core\Request;

class Dispatcher extends BaseDispatcher {
    protected function throwNotFoundError(Request $request): void {
        [$moduleName, $controllerName, $actionName] = $request->handler();
        $message = [];
        if (!$moduleName) {
            $message[] = 'module name is empty';
        }
        if (!$controllerName) {
            $message[] = 'controller name is empty';
        }
        if (!$actionName) {
            $message[] = 'action name is empty';
        }
        if (!count($message)) {
            $message[] = 'unknown';
        }
        throw new NotFoundException("Reason: " . implode(", ", $message));
    }
}