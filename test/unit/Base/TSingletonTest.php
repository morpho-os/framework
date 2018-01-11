<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Base {
    use Morpho\Test\TestCase;
    use Morpho\Qa\Test\Unit\Base\TSingletonTest\Singleton;

    class TSingletonTest extends TestCase {
        public function testSingleton() {
            $instance = Singleton::instance();
            $this->assertInstanceOf(Singleton::class, $instance);
            $this->assertSame($instance, Singleton::instance());

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            $this->assertNull(Singleton::resetInstance());

            $newInstance = Singleton::instance();
            $this->assertNotSame($instance, $newInstance);
            $this->assertInstanceOf(Singleton::class, $newInstance);
            $this->assertSame($newInstance, Singleton::instance());
        }
    }
}

namespace Morpho\Qa\Test\Unit\Base\TSingletonTest {
    use Morpho\Base\TSingleton;

    class Singleton {
        use TSingleton;
    }
}

