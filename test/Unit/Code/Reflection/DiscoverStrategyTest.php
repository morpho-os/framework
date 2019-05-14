<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Code\Reflection;

use Morpho\Testing\TestCase;

abstract class DiscoverStrategyTest extends TestCase {
    /**
     * @var \Morpho\Code\Reflection\IDiscoverStrategy
     */
    private $strategy;

    public function setUp(): void {
        parent::setUp();
        $this->strategy = $this->mkDiscoverStrategy();
    }

    public function testClassTypesDefinedInFile() {
        $expected = [
            __NAMESPACE__ . '\\StrategyTest1\\FooTrait',
            __NAMESPACE__ . '\\StrategyTest1\\BarClass',
            __NAMESPACE__ . '\\StrategyTest2\\BazInterface',
        ];
        $actual = $this->strategy->classTypesDefinedInFile(__DIR__ . '/_files/DiscoverStrategyTest/MyFile.php');
        \sort($expected);
        \sort($actual);
        $this->assertEquals($expected, $actual);
    }

    abstract protected function mkDiscoverStrategy();
}
