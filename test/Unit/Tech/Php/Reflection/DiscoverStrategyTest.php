<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php\Reflection;

use Morpho\Testing\TestCase;

abstract class DiscoverStrategyTest extends TestCase {
    /**
     * @var \Morpho\Tech\Php\Reflection\IDiscoverStrategy
     */
    protected $strategy;

    public function setUp(): void {
        parent::setUp();
        $this->strategy = $this->mkDiscoverStrategy();
    }

    public function dataForClassTypesDefinedInFile() {
        yield [
            [
                __NAMESPACE__ . '\\StrategyTest1\\FooTrait',
                __NAMESPACE__ . '\\StrategyTest1\\BarClass',
                __NAMESPACE__ . '\\StrategyTest2\\BazInterface',
            ],
            'MyFile.php',
        ];
        yield [
            [
                'Morpho_Test_Unit_Tech_Php_ReflectionStrategyTest1_Foo',
                'Morpho_Test_Unit_Tech_Php_ReflectionStrategyTest1\\Bar',
                __NAMESPACE__ . '\\StrategyTest1\\Baz',
            ],
            'mixed-nss.php',
        ];
    }

    /**
     * @dataProvider dataForClassTypesDefinedInFile
     */
    public function testClassTypesDefinedInFile(array $expected, string $relFilePath) {
        $actual = $this->strategy->classTypesDefinedInFile(__DIR__ . '/_files/DiscoverStrategyTest/' . $relFilePath);
        $this->assertEquals($expected, $actual);
    }

    abstract protected function mkDiscoverStrategy();
}
