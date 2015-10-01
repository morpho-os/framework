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

    public function register() {
        if ($this->registered) {
            throw new \LogicException();
        }
        HandlerManager::register(HandlerManager::EXCEPTION, array($this, 'handleException'));
        $this->registered = true;
    }

    public function unregister() {
        if (!$this->registered) {
            throw new \LogicException();
        }
        HandlerManager::unregister(HandlerManager::EXCEPTION, array($this, 'handleException'));
    }

    public function handleException(\Throwable $e) {
        foreach ($this->listeners as $listener) {
            $listener->onException($e);
        }
    }

    public function attachListener(IExceptionListener $handler, $prepend = false) {
        if ($prepend) {
            array_unshift($this->listeners, $handler);
        } else {
            $this->listeners[] = $handler;
        }
    }
}
