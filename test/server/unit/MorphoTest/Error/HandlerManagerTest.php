<?php
namespace MorphoTest\Error;

use Morpho\Error\HandlerManager;

require_once __DIR__ . '/BaseErrorHandlerTest.php';

class HandlerManagerTest extends BaseErrorHandlerTest {
    public function testIsRegistered() {
        $callback = array($this, 'myHandler');
        $handlerTypes = [HandlerManager::ERROR, HandlerManager::EXCEPTION];
        foreach ($handlerTypes as $handlerType) {
            $this->assertFalse(HandlerManager::isRegistered($handlerType, $callback));
            HandlerManager::register($handlerType, $callback);
            $this->assertTrue(HandlerManager::isRegistered($handlerType, $callback));
        }
    }

    public function testGetAllDoesNotChangeCurrentHandlers() {
        $this->assertEquals(1, count(HandlerManager::getAll('error')));
        $this->assertEquals(0, count(HandlerManager::getAll('exception')));
        $this->assertEquals(1, count(HandlerManager::getAll('error')));
        $this->assertEquals(0, count(HandlerManager::getAll('exception')));
    }

    public function testCanCallCurrentHandlerWithListOfArgs() {
        HandlerManager::register(HandlerManager::EXCEPTION, array($this, 'myHandler'));
        HandlerManager::callCurrent(HandlerManager::EXCEPTION, array('one', 'two', 'three'));
        $this->assertEquals(array('one', 'two', 'three'), $this->handlerArgs);
    }

    public function testRegisterAndUnregisterHandler() {
        $this->assertEquals($this->prevErrorHandler, HandlerManager::getCurrent(HandlerManager::ERROR));

        $callback = array($this, 'myHandler');
        $this->assertEquals($this->prevErrorHandler, HandlerManager::register(HandlerManager::ERROR, $callback));

        echo $t;

        $expected = array(
            E_NOTICE,
            "Undefined variable: t",
            __FILE__,
            __LINE__ - 6,
        );
        array_pop($this->handlerArgs);
        $this->assertEquals($expected, $this->handlerArgs);
        $this->assertEquals($callback, HandlerManager::getCurrent(HandlerManager::ERROR));

        HandlerManager::unregister(HandlerManager::ERROR, $callback);
        $this->assertEquals($this->prevErrorHandler, HandlerManager::getCurrent(HandlerManager::ERROR));
    }

    public function testThrowsExceptionIfInvalidHandlerTypeProvided() {
        $class = '\Morpho\Error\HandlerManager';
        $methods = array_diff(get_class_methods($class), ['getAllExceptionHandlers', 'getAllErrorHandlers']);
        $callback = array($this, 'myHandler');
        foreach ($methods as $method) {
            try {
                call_user_func(array($class, $method), 'invalid-type', $callback);
                $this->fail();
            } catch (\InvalidArgumentException $e) {
                $this->assertEquals("Invalid handler type was provided 'invalid-type'.", $e->getMessage());
            }
        }
    }
}
