<?php
namespace Morpho\Error;

interface IErrorHandler extends IExceptionHandler {
    public function handleError($level, $message, $filePath, $line, $context): void;

    public function handleFatalError(): void;
}
