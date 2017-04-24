<?php declare(strict_types=1);
namespace MorphoTest\Error;

use Morpho\Error\HandlerManager;

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
        $this->assertEquals(1, count(HandlerManager::handlersOfType('error')));
        $this->assertEquals(0, count(HandlerManager::handlersOfType('exception')));
        $this->assertEquals(1, count(HandlerManager::handlersOfType('error')));
        $this->assertEquals(0, count(HandlerManager::handlersOfType('exception')));
    }

    public function testRegisterAndUnregisterHandler() {
        $this->assertEquals($this->prevErrorHandler, HandlerManager::handlerOfType(HandlerManager::ERROR));

        $callback = [$this, 'myHandler'];
        $this->assertEquals($this->prevErrorHandler, HandlerManager::registerHandler(HandlerManager::ERROR, $callback));

        echo $t;

        $expected = [
            E_NOTICE,
            "Undefined variable: t",
            __FILE__,
            __LINE__ - 6,
        ];
        array_pop($this->handlerArgs);
        $this->assertEquals($expected, $this->handlerArgs);
        $this->assertEquals($callback, HandlerManager::handlerOfType(HandlerManager::ERROR));

        HandlerManager::unregisterHandler(HandlerManager::ERROR, $callback);
        $this->assertEquals($this->prevErrorHandler, HandlerManager::handlerOfType(HandlerManager::ERROR));
    }

    public function testThrowsExceptionIfInvalidHandlerTypeProvided() {
        $class = '\Morpho\Error\HandlerManager';
        $methods = array_diff(get_class_methods($class), ['exceptionHandlers', 'errorHandlers', 'exceptionHandler', 'errorHandler']);
        $callback = [$this, 'myHandler'];
        foreach ($methods as $method) {
            try {
                call_user_func([$class, $method], 'invalid-type', $callback);
                $this->fail($class . '::' . $method . '() does not throw \InvalidArgumentException');
            } catch (\InvalidArgumentException $e) {
                $this->assertEquals("Invalid handler type was provided 'invalid-type'.", $e->getMessage());
            }
        }
    }
}
