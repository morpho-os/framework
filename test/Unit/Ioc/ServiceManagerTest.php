<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Ioc;

use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\ServiceNotFoundException;
use Morpho\Testing\TestCase;
use Morpho\Ioc\ServiceManager;

class ServiceManagerTest extends TestCase {
    private $serviceManager;

    public function setUp(): void {
        parent::setUp();
        $this->serviceManager = new MyServiceManager;
    }

    public function testArrayAccess() {
        $this->assertInstanceOf(\ArrayObject::class, $this->serviceManager);
        $id = __FUNCTION__;
        $value = 'bar';
        $this->assertFalse(isset($this->serviceManager[$id]));
        $this->serviceManager[$id] = $value;
        $this->assertSame($value, $this->serviceManager[$id]);
        $this->assertTrue(isset($this->serviceManager[$id]));
        unset($this->serviceManager[$id]);
        $this->assertFalse(isset($this->serviceManager[$id]));
    }

    public function testArrayAccess_OffsetExists_ReturnsTrueIfContainerCanReturnEntryForId() {
        // See [PHP docs for the ContainerInterface::has()](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md#31-psrcontainercontainerinterface)
        $this->assertTrue(isset($this->serviceManager['foo']));
    }

    public function testConstructor_SetsServiceManagerIfServiceImplementsServiceManagerInterface() {
        $service = new class implements IHasServiceManager {
            private $serviceManager;

            public function setServiceManager(IServiceManager $serviceManager): void {
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
        $this->expectException('\RuntimeException', "Circular reference detected for the service 'foo', path: 'foo -> bar -> foo'");
        $this->serviceManager['foo'];
    }

    public function testReturnsTheSameInstance() {
        $obj1 = $this->serviceManager['obj'];
        $obj2 = $this->serviceManager['obj'];
        $this->assertSame($obj1, $obj2);
        $this->assertInstanceOf('\stdClass', $obj1);
    }

    public function testThrowsExceptionWhenServiceNotFound() {
        $this->expectException(ServiceNotFoundException::class);
        $this->serviceManager['nonexistent'];
    }

    public function testCreateServiceMethodCanReturnClosure() {
        $closure = $this->serviceManager['myClosure'];
        $this->assertInstanceOf('\Closure', $closure);
        $this->assertSame($closure, $this->serviceManager['myClosure']);

        $this->assertNull($this->serviceManager->closureCalledWith);
        $closure('my arg');
        $this->assertEquals('my arg', $this->serviceManager->closureCalledWith);
    }
}

class MyServiceManager extends ServiceManager {
    public $closureCalledWith;

    protected function mkObjService() {
        return new \stdClass();
    }

    protected function mkFooService() {
        return $this['bar'];
    }

    protected function mkBarService() {
        return $this['foo'];
    }

    protected function mkMyClosureService() {
        return function ($foo) {
            $this->closureCalledWith = $foo;
        };
    }
}
