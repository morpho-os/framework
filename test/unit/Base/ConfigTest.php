<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Base;

use Morpho\Base\Config;
use Morpho\Test\TestCase;

class ConfigTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(\ArrayObject::class, new Config());
    }

    public function testOnlyDefault() {
        $config = new class extends Config {
            protected $default = [
                'foo' => 'bar',
            ];
        };
        $this->assertSame(['foo' => 'bar'], $config->getArrayCopy());
    }

    public function testDefaultWithValues() {
        $config = new class (['abc' => 123, 'foo' => 'pear']) extends Config {
            protected $default = [
                'foo' => 'bar',
            ];
        };
        $this->assertSame(['abc' => 123, 'foo' => 'pear'], $config->getArrayCopy());
    }

    public function testOnlyValues() {
        $data = ['foo' => 'bar'];
        $config = new Config($data);
        $this->assertSame($data, $config->getArrayCopy());
    }

    public function testNoDefaultAndValues() {
        $this->assertSame([], (new Config())->getArrayCopy());
    }

    public function dataForMerge() {
        yield [
            false,
            ['foo' => ['abc']],
        ];
        yield [
            true,
            ['foo' => ['bar', 'abc']],
        ];
    }

    /**
     * @dataProvider dataForMerge
     */
    public function testMerge(bool $recursive, $expected) {
        $config = new Config(['foo' => ['bar']]);
        $this->assertSame($expected, $config->merge(['foo' => ['abc']], $recursive)->getArrayCopy());
    }
}