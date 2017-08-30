<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Error;

interface IExceptionHandler {
    public function register(): void;

    public function unregister(): void;

    public function attachListener(IExceptionListener $listener, $prepend = false): void;

    public function handleException(\Throwable $e): void;
}
