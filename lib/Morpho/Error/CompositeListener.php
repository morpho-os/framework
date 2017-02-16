<?php
namespace Morpho\Error;

class CompositeListener implements IExceptionListener {
    protected $listeners;

    public function __construct(array $listeners) {
        $this->listeners = $listeners;
    }

    public function onException(\Throwable $exception): void {
        foreach ($this->listeners as $listener) {
            $listener->onException($exception);
        }
    }
}