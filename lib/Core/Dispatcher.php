<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\IFn;

abstract class Dispatcher {
    /**
     * @var int
     */
    protected $maxNoOfDispatchIterations = 30;

    /**
     * @var ModuleProvider
     */
    protected $moduleProvider;
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(\ArrayObject $moduleProvider, EventManager $eventManager) {
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

                /** @var Controller $controller */
                $controller = $this->controller(...$request->handler());
                $controller->__invoke($request);

                $this->eventManager->trigger(new Event('afterDispatch', ['request' => $request]));
            } catch (\Throwable $e) {
                $this->eventManager->trigger(new Event('dispatchError', ['request' => $request, 'exception' => $e]));
            }
            $i++;
        } while (false === $request->isDispatched());
    }

    abstract protected function trigger(string $eventName, array $args = null);

    protected function controller(?string $moduleName, ?string $controllerName, ?string $actionName): IFn {
        if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
            $this->actionNotFound($moduleName, $controllerName, $actionName);
        }
        $module = $this->moduleProvider->offsetGet($moduleName);
        return $module->offsetGet($controllerName);
    }

    abstract protected function actionNotFound(?string $moduleName, ?string $controllerName, ?string $actionName);
}