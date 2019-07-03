<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\JsonResult;
use Morpho\Testing\TestCase;

class JsonResultTest extends TestCase {
    public function testJsonSerialize_SerializesRecursiveIfValIsJsonSerializable() {
        $val = new class implements \JsonSerializable {
            public $jsonSerializeCalled;

            public function jsonSerialize() {
                $this->jsonSerializeCalled = true;
                return ['foo' => 'bar'];
            }
        };
        $jsonResult = new JsonResult($val);
        $encoded = \json_encode($jsonResult);
        $this->assertSame('{"foo":"bar"}', $encoded);
        $this->assertTrue($val->jsonSerializeCalled);
    }
}
