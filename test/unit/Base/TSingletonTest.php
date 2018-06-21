<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base {

    use Morpho\Test\Unit\Base\TSingletonTest\ChildSingleton;
    use Morpho\Testing\TestCase;
    use Morpho\Test\Unit\Base\TSingletonTest\Singleton;

    class TSingletonTest extends TestCase {
        public function tearDown() {
            parent::tearDown();
            Singleton::resetState();
        }

        public function testSingleton() {
            $instance = Singleton::instance();
            $this->assertInstanceOf(Singleton::class, $instance);
            $this->assertSame($instance, Singleton::instance());

            /** @noinspection PhpVoidFunctionResultUsedInspection */
            $this->assertNull(Singleton::resetState());

            $newInstance = Singleton::instance();
            $this->assertNotSame($instance, $newInstance);
            $this->assertInstanceOf(Singleton::class, $newInstance);
            $this->assertSame($newInstance, Singleton::instance());
        }

        public function testInheritedSingleton() {
            $instance = ChildSingleton::instance();
            $this->assertInstanceOf(ChildSingleton::class, $instance);
            $this->assertSame($instance, ChildSingleton::instance());
        }
    }
}

namespace Morpho\Test\Unit\Base\TSingletonTest {
    use Morpho\Base\TSingleton;

    class Singleton {
        use TSingleton;
    }

    class ChildSingleton extends Singleton {
    }
}

