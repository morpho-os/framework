<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Network;

use Morpho\Network\Address;
use Morpho\Test\TestCase;

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
        $address = new Address($host, $port);

        $this->assertSame($host, $address->host());
        $this->assertSame($port, $address->port());

        $this->assertSame($host . (null === $port ? '' : ':' . $port), $address->__toString());
    }

    /**
     * @dataProvider dataForToString
     */
    public function testParse(string $host, ?int $port) {
        $address = Address::parse($host . (null === $port ? '' : ':' . $port));
        $this->assertEquals(
            new Address($host, $port),
            $address
        );
        $this->assertSame($host, $address->host());
        $this->assertSame($port, $address->port());
    }
}