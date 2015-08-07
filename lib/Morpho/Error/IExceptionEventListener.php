<?php
namespace Morpho\Error;

interface IExceptionEventListener {
    public function onException(\Throwable $exception);
}
