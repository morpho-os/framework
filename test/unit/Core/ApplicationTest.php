<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core {

    use Morpho\Ioc\IServiceManager;
    use Morpho\Test\TestCase;
    use MorphoTest\Unit\Core\ApplicationTest\App;

    class ApplicationTest extends TestCase {
        public function testConfigAccessors() {
            $app = new App();
            $this->assertEquals(new \ArrayObject([]), $app->config());

            $newConfig = new \ArrayObject(['foo' => 'bar']);
            $app = new App($newConfig);
            $this->assertSame($newConfig, $app->config());

            $newConfig = new \ArrayObject(['color' => 'orange']);
            $this->assertNull($app->setConfig($newConfig));
            $this->assertSame($newConfig, $app->config());
        }

        public function testGet_DelegatesToCustomServiceManagerIfItWasSet() {
            $app = new App();
            $serviceManager = $this->createMock(IServiceManager::class);
            $val = new \stdClass();
            $serviceManager->expects($this->once())
                ->method('get')
                ->with('foo')
                ->willReturn($val);
            $app->setServiceManager($serviceManager);
            $this->assertSame($val, $app->get('foo'));
        }

        public function testGet_DelegatesToDefaultServiceManagerIfItWasNotSet() {
            $app = new App();
            $this->assertSame('bar', $app->get('foo'));
        }
    }
}

namespace MorphoTest\Unit\Core\ApplicationTest {
    use Morpho\Base\NotImplementedException;
    use Morpho\Core\Application;
    use Morpho\Ioc\IServiceManager;
    use Morpho\Ioc\ServiceManager as BaseServiceManager;

    class App extends Application {
        protected function showError(\Throwable $e): void {
            throw new NotImplementedException();
        }

        protected function newServiceManager(): IServiceManager {
            return new ServiceManager();
        }
    }

    class ServiceManager extends BaseServiceManager {
        public function newFooService() {
            return 'bar';
        }
    }
}
