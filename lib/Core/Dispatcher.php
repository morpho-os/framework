<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\Event;
use Morpho\Base\IEventManager;
use Morpho\Base\IFn;

abstract class Dispatcher {
    /**
     * @var int
     */
    protected $maxNoOfDispatchIterations = 20;

    /**
     * @var \ArrayObject
     */
    protected $moduleProvider;

    /**
     * @var IEventManager
     */
    private $eventManager;

    public function __construct(\ArrayObject $moduleProvider, IEventManager $eventManager) {
        $this->moduleProvider = $moduleProvider;
        $this->eventManager = $eventManager;
    }

    public function dispatch(Request $request): void {
        $i = 0;
        do {
            if ($i >= $this->maxNoOfDispatchIterations) {
                throw new \RuntimeException("Dispatch loop has occurred {$this->maxNoOfDispatchIterations} times");
            }
            try {
                $request->isDispatched(true);

                $this->eventManager->trigger(new Event('beforeDispatch', ['request' => $request]));

                [$moduleName, $controllerName, $actionName] = $request->handler();
                if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
                    $this->throwNotFoundError($moduleName, $controllerName, $actionName);
                }
                $handler = $this->handler($moduleName, $controllerName, $actionName);
                $handler->__invoke($request);

                $this->eventManager->trigger(new Event('afterDispatch', ['request' => $request]));
            } catch (\Throwable $e) {
                $this->eventManager->trigger(new Event('dispatchError', ['request' => $request, 'exception' => $e]));
            }
            $i++;
        } while (false === $request->isDispatched());
    }

    public function setMaxNoOfDispatchIterations(int $n): void {
        $this->maxNoOfDispatchIterations = $n;
    }

    public function maxNoOfDispatchIterations(): int {
        return $this->maxNoOfDispatchIterations;
    }

    protected function handler(?string $moduleName, ?string $controllerName, ?string $actionName): IFn {
        $module = $this->moduleProvider->offsetGet($moduleName);
        return $module->offsetGet($controllerName);
    }

    abstract protected function throwNotFoundError(?string $moduleName, ?string $controllerName, ?string $actionName): void;
}