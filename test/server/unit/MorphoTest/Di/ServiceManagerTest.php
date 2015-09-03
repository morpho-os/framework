<?php
namespace MorphoTest\Di;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;
use Morpho\Test\TestCase;
use Morpho\Di\ServiceManager;

class ServiceManagerTest extends TestCase {
    public function setUp() {
        $this->serviceManager = new MyServiceManager;
    }

    public function testConstructor_SetsServiceManagerIfServiceImplementsServiceManagerInterface() {
        $service = new class implements IServiceManagerAware {
            private $serviceManager;

            public function setServiceManager(IServiceManager $serviceManager) {
                $this->serviceManager = $serviceManager;
            }

            public function isServiceManagerSet() {
                return $this->serviceManager instanceof IServiceManager;
            }
        };
        $this->assertFalse($service->isServiceManagerSet());
        new ServiceManager(['foo' => $service]);
        $this->assertTrue($service->isServiceManagerSet());
    }

    public function testCanDetectCircularReference() {
        $this->setExpectedException('\RuntimeException', "Circular reference detected for the service 'foo', path: 'foo -> bar'.");
        $this->serviceManager->get('foo');
    }

    public function testReturnsTheSameInstance() {
        $obj1 = $this->serviceManager->get('obj');
        $obj2 = $this->serviceManager->get('obj');
        $this->assertSame($obj1, $obj2);
        $this->assertInstanceOf('\stdClass', $obj1);
    }

    /*
    public function testCanInstantiateFromFactory() {
        $called = false;
        $this->serviceManager->setFactory('router', function () use (&$called) {
            $called = true;
            return new \stdClass();
        });
        $this->assertFalse($called);
        $this->assertInstanceOf('\stdClass', $this->serviceManager->get('router'));
        $this->assertTrue($called);
    }
    */

    public function testThrowsExceptionWhenServiceNotFound() {
        $this->setExpectedException('\Morpho\Di\ServiceNotFoundException');
        $this->serviceManager->get('nonexistent');
    }

    public function testCreateServiceMethodCanReturnClosure() {
        $closure = $this->serviceManager->get('myClosure');
        $this->assertInstanceOf('\Closure', $closure);
        $this->assertSame($closure, $this->serviceManager->get('myClosure'));

        $this->assertNull($this->serviceManager->closureCalledWith);
        $closure('my arg');
        $this->assertEquals('my arg', $this->serviceManager->closureCalledWith);
    }
}

class MyServiceManager extends ServiceManager {
    public $closureCalledWith;

    protected function createObjService() {
        return new \stdClass();
    }

    protected function createFooService() {
        return $this->get('bar');
    }

    protected function createBarService() {
        return $this->get('foo');
    }

    protected function createMyClosureService() {
        return function ($foo) {
            $this->closureCalledWith = $foo;
        };
    }
}
