<?php declare(strict_types=1);
namespace MorphoTest\Unit\Core;

use Morpho\Test\TestCase;
use Morpho\Core\Version;

class VersionTest extends TestCase {
    public function dataForIsValid_StringArg() {
        return [
            [true, '0'],
            [true, '0.0.0'],
            [true, '1'],
            [true, '0.1'],
            [true, '0.1.1'],
/*
            [false, 'abc0.1.1'],

            [false, '0.'],
            [false, '0.0.0.'],
            [false, '1.'],
            [false, '0.1.'],
            [false, '0.1.1.'],
            [false, '0.1.1.1.'],

            [true, '7.x-1.0-release1'],
            [true, '0.1-dev'],
            [true, '0.1-dev1'],
            [true, '0.1-alpha'],
            [true, '0.1-alpha1'],
            [true, '0.1-beta'],
            [true, '0.1-beta1'],
            [true, '0.1-rc'],
            [true, '0.1-rc1'],
*/
        ];
    }

    /**
     * @dataProvider dataForIsValid_StringArg
     */
    public function testIsValid_StringArg(bool $expected, string $version) {
        $this->assertEquals($expected, Version::isValid($version));
    }

    public function testToString() {
        $this->assertEquals('1.2.3', (string)(new Version('1', '2', '3', null)));
        // @TODO: More tests, (string)(new Version(1)) -> 1.0.0??
    }
}
