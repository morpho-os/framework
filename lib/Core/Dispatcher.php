<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\Event;
use Morpho\Base\IEventManager;

abstract class Dispatcher {
    /**
     * @var int
     */
    protected $maxNoOfDispatchIterations = 20;

    /**
     * @var \ArrayObject
     */
    protected $handlerProvider;

    /**
     * @var IEventManager
     */
    private $eventManager;

    public function __construct(callable $handlerProvider, IEventManager $eventManager) {
        $this->handlerProvider = $handlerProvider;
        $this->eventManager = $eventManager;
    }

    public function dispatch(Request $request): void {
        $i = 0;
        do {
            if ($i >= $this->maxNoOfDispatchIterations) {
                throw new \RuntimeException("Dispatch loop has occurred, iterated {$this->maxNoOfDispatchIterations} times");
            }
            try {
                $request->isDispatched(true);

                $this->eventManager->trigger(new Event('beforeDispatch', ['request' => $request]));

                $handler = ($this->handlerProvider)($request);
                if (false === $handler) {
                    $this->throwNotFoundError($request);
                }
                $handler($request);

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

    /**
     * @throws \RuntimeException
     */
    abstract protected function throwNotFoundError(Request $request): void;
}