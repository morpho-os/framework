<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Test\Unit\App\Web\TActionResultTest;
use Morpho\App\Web\View\JsonResult;
use Morpho\Testing\TestCase;

class JsonResultTest extends TestCase {
    use TActionResultTest;

    public function testJsonSerialize_SerializesRecursiveIfValIsJsonSerializable() {
        $val = new class implements \JsonSerializable {
            public $jsonSerializeCalled;

            public function jsonSerialize() {
                $this->jsonSerializeCalled = true;
                return ['foo' => 'bar'];
            }
        };
        $actionResult = new JsonResult($val);
        $encoded = \json_encode($actionResult);
        $this->assertSame('{"foo":"bar"}', $encoded);
        $this->assertTrue($val->jsonSerializeCalled);
    }

    public function dataForInvoke() {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider dataForInvoke
     */
    public function testInvoke(bool $isAjax) {
        $val = ['foo' => 'bar'];
        $actionResult = new JsonResult($val);
        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, $isAjax);
        $serviceManager = [
            'request' => $request,
            'jsonRenderer' => function () use (&$called) {
                $called = true;
            },
        ];

        $actionResult->__invoke($serviceManager);

        $this->assertTrue($called);
        $this->assertIsObject($response['result']);
    }
}
