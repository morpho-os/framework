<?php
namespace Morpho\Error;

interface IExceptionListener {
    public function onException(\Throwable $exception): void;
}
