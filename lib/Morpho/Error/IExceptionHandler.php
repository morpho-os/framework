<?php
namespace Morpho\Error;

interface IExceptionHandler {
    public function register();

    public function unregister();

    public function attach($handler);

    public function handleException(\Throwable $e);
}
