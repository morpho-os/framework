<?php
namespace Morpho\Error;

interface IExceptionHandler {
    public function register();

    public function unregister();

    public function attachListener(IExceptionEventListener $listener, $prepend = false);

    public function handleException(\Throwable $e);
}
