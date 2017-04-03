<?php
namespace MorphoTest\Code\ClassTypeDiscoverer;

use Morpho\Test\TestCase;

abstract class DiscoverStrategyTest extends TestCase {
    private $strategy;

    public function setUp() {
        $this->strategy = $this->createDiscoverStrategy();
    }

    public function testClassTypesDefinedInFile() {
        $expected = [
            __NAMESPACE__ . '\\StrategyTest1\\FooTrait',
            __NAMESPACE__ . '\\StrategyTest1\\BarClass',
            __NAMESPACE__ . '\\StrategyTest2\\BazInterface',
        ];
        $actual = $this->strategy->classTypesDefinedInFile(__DIR__ . '/_files/DiscoverStrategyTest/MyFile.php');
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    abstract protected function createDiscoverStrategy();
}
