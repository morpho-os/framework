<?php
namespace Morpho\Error;

class ExceptionHandler implements IExceptionHandler {
    private $registered = false;

    protected $listeners = [];

    public function __construct(array $listeners = null) {
        if (null === $listeners) {
            $listeners = [new DumpListener()];
        }
        if (!count($listeners)) {
            throw new \LogicException();
        }
        foreach ($listeners as $listener) {
            $this->attachListener($listener);
        }
    }

    public function register(): void {
        if ($this->registered) {
            throw new \LogicException();
        }
        HandlerManager::register(HandlerManager::EXCEPTION, [$this, 'handleException']);
        $this->registered = true;
    }

    public function unregister(): void {
        if (!$this->registered) {
            throw new \LogicException();
        }
        HandlerManager::unregister(HandlerManager::EXCEPTION, [$this, 'handleException']);
    }

    public function handleException(\Throwable $e): void {
        foreach ($this->listeners as $listener) {
            $listener->onException($e);
        }
    }

    public function attachListener(IExceptionListener $handler, $prepend = false): void {
        if ($prepend) {
            array_unshift($this->listeners, $handler);
        } else {
            $this->listeners[] = $handler;
        }
    }
}
