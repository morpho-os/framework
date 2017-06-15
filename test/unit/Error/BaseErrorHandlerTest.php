<?php declare(strict_types=1);
namespace MorphoTest\Error;

use Morpho\Test\TestCase;

abstract class BaseErrorHandlerTest extends TestCase {
    protected $prevErrorHandler, $prevExceptionHandler, $handlerArgs;

    public function setUp() {
        $handler = set_error_handler([$this, 'setUp']);
        restore_error_handler();
        $this->prevErrorHandler = $handler;

        $handler = set_exception_handler([$this, 'setUp']);
        restore_exception_handler();
        $this->prevExceptionHandler = $handler;

        unset($this->handlerArgs);
    }

    public function tearDown() {
        \Morpho\Error\HandlerManager::unregisterHandler('error', $this->prevErrorHandler);
        \Morpho\Error\HandlerManager::unregisterHandler('exception', $this->prevExceptionHandler);
    }

    public function myHandler() {
        $this->handlerArgs = func_get_args();
    }
}
