<?php
declare(strict_types=1);
namespace MorphoTest\Web;

use InvalidArgumentException;
use Morpho\Test\TestCase;
use Morpho\Web\HttpClient;

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