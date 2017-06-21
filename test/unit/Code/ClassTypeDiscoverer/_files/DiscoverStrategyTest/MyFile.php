<?php
namespace MorphoTest\Unit\Code\ClassTypeDiscoverer\StrategyTest1;

trait FooTrait {
    public function test() {

    }
}

class BarClass {
    public function doSomething() {
        // Some Discover strategies can incorrectly parse the next statement.
        return [
            self::class . '\\MyClass',
            BarClass::class,
            new class {},
        ];
    }
}

namespace MorphoTest\Unit\Code\ClassTypeDiscoverer\StrategyTest2;

interface BazInterface {
}
