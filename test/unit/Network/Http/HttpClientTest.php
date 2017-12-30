<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Network\Http;

use InvalidArgumentException;
use Morpho\Test\TestCase;
use Morpho\Network\Http\HttpClient;

class HttpClientTest extends TestCase {
    public function testMaxNumberOfRedirects_Accessors() {
        $client = new HttpClient();
        $this->assertEquals(5, $client->maxNumberOfRedirects());
        $n = 0;
        $this->assertSame($client, $client->setMaxNumberOfRedirects($n));
        $this->assertEquals($n, $client->maxNumberOfRedirects());
    }

    public function testMaxNumberOfRedirects_LowerBound() {
        $client = new HttpClient();
        $this->expectException(InvalidArgumentException::class, "The value must be >= 0");
        $client->setMaxNumberOfRedirects(-1);
    }
}