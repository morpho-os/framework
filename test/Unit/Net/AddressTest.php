<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Net;

use Morpho\Net\TcpAddress;
use Morpho\Testing\TestCase;

class AddressTest extends TestCase {
    public function dataForToString() {
        return [
            [
                'baz',
                9981
            ],
            [
                '[2001:db8::1]',
                80
            ],
            [
                '127.0.0.1',
                8951
            ],
            [
                '127.0.0.1',
                null,
            ],
            [
                '::1',
                35217
            ],
            [
                'foo',
                null,
            ],
        ];
    }

    /**
     * @dataProvider dataForToString
     */
    public function testToString(string $host, ?int $port) {
        $address = new TcpAddress($host, $port);

        $this->assertSame($host, $address->host());
        $this->assertSame($port, $address->port());

        $this->assertSame($host . (null === $port ? '' : ':' . $port), $address->__toString());
    }

    /**
     * @dataProvider dataForToString
     */
    public function testParse(string $host, ?int $port) {
        $address = TcpAddress::parse($host . (null === $port ? '' : ':' . $port));
        $this->assertEquals(
            new TcpAddress($host, $port),
            $address
        );
        $this->assertSame($host, $address->host());
        $this->assertSame($port, $address->port());
    }
}
