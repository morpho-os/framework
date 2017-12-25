<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Environment;

class EnvironmentTest extends TestCase {
    public function dataForHttpVersion() {
        yield [
            'HTTP/1.0',
            'HTTP/1.0',
        ];
        yield [
            'HTTP/1.1',
            'HTTP/1.1',
        ];
        yield [
            'HTTP/2.0',
            'HTTP/2.0',
        ];
        yield [
            'HTTP/invalid',
            Environment::HTTP_VERSION,
        ];
        yield [
            'invalid',
            Environment::HTTP_VERSION,
        ];
        yield [
            'HTTP/10.1',
            Environment::HTTP_VERSION,
        ];
    }

    /**
     * @dataProvider dataForHttpVersion
     */
    public function testHttpVersion(string $serverProtocol, string $expected) {
        $_SERVER['SERVER_PROTOCOL'] = $serverProtocol;
        $this->assertSame($expected, Environment::httpVersion());
    }
}