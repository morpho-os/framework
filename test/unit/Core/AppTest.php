<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Core {
    use Morpho\Test\TestCase;
    use Morpho\Qa\Test\Unit\Core\AppTest\App;

    class AppTest extends TestCase {
        public function testConfigAccessors() {
            $app = new App();
            $this->assertEquals(new \ArrayObject([]), $app->config());

            $newConfig = new \ArrayObject(['foo' => 'bar']);
            $app = new App($newConfig);
            $this->assertSame($newConfig, $app->config());

            $newConfig = new \ArrayObject(['color' => 'orange']);
            /** @noinspection PhpVoidFunctionResultUsedInspection */
            $this->assertNull($app->setConfig($newConfig));
            $this->assertSame($newConfig, $app->config());
        }
    }
}

namespace Morpho\Qa\Test\Unit\Core\AppTest {
    use Morpho\Base\NotImplementedException;
    use Morpho\Core\App as BaseApp;
    use Morpho\Core\IBootstrapFactory;
    use Morpho\Ioc\IServiceManager;
    use Morpho\Ioc\ServiceManager as BaseServiceManager;

    class App extends BaseApp {
        protected function showError(\Throwable $e): void {
            throw new NotImplementedException();
        }

        protected function newServiceManager(): IServiceManager {
            return new ServiceManager();
        }

        protected function newBootstrapFactory(): IBootstrapFactory {
            throw new NotImplementedException();
        }
    }

    class ServiceManager extends BaseServiceManager {
        public function newFooService() {
            return 'bar';
        }
    }
}
