<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php;

use InvalidArgumentException;
use Morpho\Tech\Php\HandlerManager;
use PHPUnit\Util\ErrorHandler;
use RuntimeException;

use function array_diff;
use function array_shift;
use function call_user_func;
use function get_class_methods;
use function set_error_handler;

require_once __DIR__ . '/BaseErrorHandlerTest.php';

class HandlerManagerTest extends BaseErrorHandlerTest {
    public function testIsHandlerRegistered() {
        $callback = [$this, 'myHandler'];
        $handlerTypes = [HandlerManager::ERROR, HandlerManager::EXCEPTION];
        foreach ($handlerTypes as $handlerType) {
            $this->assertFalse(HandlerManager::isHandlerRegistered($handlerType, $callback));
            HandlerManager::registerHandler($handlerType, $callback);
            $this->assertTrue(HandlerManager::isHandlerRegistered($handlerType, $callback));
        }
    }

    public function testHandlersOfType_DoesNotChangeCurrentHandlers() {
        $this->assertCount(1, HandlerManager::handlersOfType(HandlerManager::ERROR));
        $this->assertCount(0, HandlerManager::handlersOfType(HandlerManager::EXCEPTION));
        $this->assertCount(1, HandlerManager::handlersOfType(HandlerManager::ERROR));
        $this->assertCount(0, HandlerManager::handlersOfType(HandlerManager::EXCEPTION));
    }

    public function testRegisterAndUnregisterHandler() {
        $this->assertEquals($this->prevErrorHandler, HandlerManager::handlerOfType(HandlerManager::ERROR));
        $callback = [$this, 'myHandler'];
        $this->assertEquals($this->prevErrorHandler, HandlerManager::registerHandler(HandlerManager::ERROR, $callback));
        echo $t;
        $expected = [\E_WARNING, 'Undefined variable $t', __FILE__, __LINE__ - 1];
        $this->assertEquals($expected, $this->handlerArgs);
        $this->assertEquals($callback, HandlerManager::handlerOfType(HandlerManager::ERROR));
        HandlerManager::unregisterHandler(HandlerManager::ERROR, $callback);
        $this->assertEquals($this->prevErrorHandler, HandlerManager::handlerOfType(HandlerManager::ERROR));
    }

    public function testUnregisterErrorHandler_OnlySecondHandler() {
        $handler1 = function () {
        };
        $handler2 = function () {
        };
        $handler3 = function () {
        };
        $handler4 = function () {
        };
        set_error_handler($handler1);
        set_error_handler($handler2);
        set_error_handler($handler3);
        set_error_handler($handler4);
        HandlerManager::unregisterHandler(HandlerManager::ERROR, $handler3);
        $this->assertSame([$handler1, $handler2, $handler4], $this->errorHandlers());
        HandlerManager::unregisterHandler(HandlerManager::ERROR, $handler1);
        $this->assertSame([$handler2, $handler4], $this->errorHandlers());
        HandlerManager::unregisterHandler(HandlerManager::ERROR, $handler2);
        $this->assertSame([$handler4], $this->errorHandlers());
        HandlerManager::unregisterHandler(HandlerManager::ERROR, $handler4);
        $this->assertSame([], $this->errorHandlers());
        try {
            HandlerManager::unregisterHandler(
                HandlerManager::ERROR,
                function () {
                }
            );
            $this->fail('Exception has not been thrown');
        } catch (RuntimeException $e) {
            $this->assertSame('Unable to unregister the error handler', $e->getMessage());
        }
    }

    private function errorHandlers() {
        $handlers = HandlerManager::handlersOfType(HandlerManager::ERROR);
        if (isset($handlers[0]) && $handlers[0] instanceof ErrorHandler) {
            array_shift($handlers);
        }
        return $handlers;
    }

    public function testThrowsExceptionIfInvalidHandlerTypeProvided() {
        $class = HandlerManager::class;
        $methods = array_diff(get_class_methods($class), ['exceptionHandlers', 'errorHandlers', 'exceptionHandler', 'errorHandler']);
        $callback = [$this, 'myHandler'];
        foreach ($methods as $method) {
            try {
                call_user_func([$class, $method], 'invalid-type', $callback);
                $this->fail($class . '::' . $method . '() does not throw \\InvalidArgumentException');
            } catch (InvalidArgumentException $e) {
                $this->assertEquals("Invalid handler type was provided 'invalid-type'.", $e->getMessage());
            }
        }
    }
}