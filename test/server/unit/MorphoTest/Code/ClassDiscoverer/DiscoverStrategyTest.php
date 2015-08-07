<?php
namespace MorphoTest\Code\ClassDiscoverer;

use Morpho\Test\TestCase;

abstract class DiscoverStrategyTest extends TestCase {
    public function __construct() {
        $this->strategy = $this->createDiscoverStrategy();
    }

    public function testGetClassesForFile() {
        $expected = array(
            __NAMESPACE__ . '\\StrategyTest1\\FooTrait',
            __NAMESPACE__ . '\\StrategyTest1\\BarClass',
            __NAMESPACE__ . '\\StrategyTest2\\BazInterface',
        );
        $actual = $this->strategy->getClassesForFile(__DIR__ . '/_files/DiscoverStrategyTest/MyFile.php');
        sort($expected);
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    abstract protected function createDiscoverStrategy();
}
