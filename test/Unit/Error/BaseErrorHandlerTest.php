<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Error;

use function func_get_args;
use function Morpho\Base\op;
use Morpho\Error\HandlerManager;
use Morpho\Testing\TestCase;
use function restore_error_handler;
use function restore_exception_handler;
use function set_error_handler;
use function set_exception_handler;

abstract class BaseErrorHandlerTest extends TestCase {
    protected $prevErrorHandler, $prevExceptionHandler, $handlerArgs;

    public function setUp(): void {
        $handler = set_error_handler([$this, __FUNCTION__]);
        restore_error_handler();
        $this->prevErrorHandler = $handler;

        $handler = set_exception_handler([$this, __FUNCTION__]);
        restore_exception_handler();
        $this->prevExceptionHandler = $handler;

        unset($this->handlerArgs);
    }

    public function tearDown(): void {
        HandlerManager::popHandlersUntil(HandlerManager::ERROR, op('===', $this->prevErrorHandler));
        HandlerManager::popHandlersUntil(HandlerManager::EXCEPTION, op('===', $this->prevExceptionHandler));
    }

    public function myHandler($severity, $message, $filePath, $lineNo) {
        $this->handlerArgs = func_get_args();
    }
}
