<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

abstract class Dispatcher {
    protected $maxNoOfDispatchIterations = 30;

    public function dispatch($request): void {
        $i = 0;
        do {
            if ($i >= $this->maxNoOfDispatchIterations) {
                throw new \RuntimeException("Dispatch loop has occurred {$this->maxNoOfDispatchIterations} times");
            }
            try {
                $request->isDispatched(true);

                $this->trigger('beforeDispatch', ['request' => $request]);

                /** @var Controller $controller */
                $controller = $this->controller(...$request->handler());
                $controller->dispatch($request);

                $this->trigger('afterDispatch', ['request' => $request]);
            } catch (\Throwable $e) {
                $this->trigger('dispatchError', ['request' => $request, 'exception' => $e]);
            }
            $i++;
        } while (false === $request->isDispatched());
    }

    abstract protected function trigger(string $eventName, array $args = null);

    abstract protected function controller(?string $moduleName, ?string $controllerName, ?string $actionName);
}