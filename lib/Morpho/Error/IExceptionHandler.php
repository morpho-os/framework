<?php
namespace Morpho\Error;

interface IExceptionHandler {
    public function register(): void;

    public function unregister(): void;

    public function attachListener(IExceptionListener $listener, $prepend = false): void;

    public function handleException(\Throwable $e): void;
}
