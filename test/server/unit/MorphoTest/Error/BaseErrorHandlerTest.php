<?php
namespace MorphoTest\Error;

use Morpho\Test\TestCase;

abstract class BaseErrorHandlerTest extends TestCase {
    public function setUp() {
        $handler = set_error_handler(array($this, 'setUp'));
        restore_error_handler();
        $this->prevErrorHandler = $handler;

        $handler = set_exception_handler(array($this, 'setUp'));
        restore_exception_handler();
        $this->prevExceptionHandler = $handler;

        unset($this->handlerArgs);
    }

    public function tearDown() {
        \Morpho\Error\HandlerManager::unregister('error', $this->prevErrorHandler);
        \Morpho\Error\HandlerManager::unregister('exception', $this->prevExceptionHandler);
    }

    public function myHandler() {
        $this->handlerArgs = func_get_args();
    }
}
