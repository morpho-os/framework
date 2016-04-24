<?php
namespace MorphoTest\Code\ClassTypeDiscoverer;

use Morpho\Test\TestCase;

abstract class DiscoverStrategyTest extends TestCase {
    public function __construct() {
        $this->strategy = $this->createDiscoverStrategy();
    }

    public function testDefinedClassTypesInFile() {
        $expected = [
            __NAMESPACE__ . '\\StrategyTest1\\FooTrait',
            __NAMESPACE__ . '\\StrategyTest1\\BarClass',
            __NAMESPACE__ . '\\StrategyTest2\\BazInterface',
        ];
        $actual = $this->strategy->definedClassTypesInFile(__DIR__ . '/_files/DiscoverStrategyTest/MyFile.php');
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    abstract protected function createDiscoverStrategy();
}
