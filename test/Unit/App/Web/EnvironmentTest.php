<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Env;
use Morpho\Testing\TestCase;

class EnvironmentTest extends TestCase {
    public function dataHttpVersion() {
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
            Env::HTTP_VERSION,
        ];
        yield [
            'invalid',
            Env::HTTP_VERSION,
        ];
        yield [
            'HTTP/10.1',
            Env::HTTP_VERSION,
        ];
    }

    /**
     * @dataProvider dataHttpVersion
     */
    public function testHttpVersion(string $serverProtocol, string $expected) {
        $_SERVER['SERVER_PROTOCOL'] = $serverProtocol;
        $this->assertSame($expected, Env::httpVersion());
    }
}
