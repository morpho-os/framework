<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\IFn;

class Dispatcher {
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

    public function setMaxNoOfDispatchIterations(int $n) {
        $this->maxNoOfDispatchIterations = $n;
        return $this;
    }

    public function maxNoOfDispatchIterations(): int {
        return $this->maxNoOfDispatchIterations;
    }

    protected function controller(?string $moduleName, ?string $controllerName, ?string $actionName): IFn {
        if (empty($moduleName) || empty($controllerName) || empty($actionName)) {
            $this->actionNotFound($moduleName, $controllerName, $actionName);
        }
        $module = $this->moduleProvider->offsetGet($moduleName);
        return $module->offsetGet($controllerName);
    }

    protected function actionNotFound(?string $moduleName, ?string $controllerName, ?string $actionName) {
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