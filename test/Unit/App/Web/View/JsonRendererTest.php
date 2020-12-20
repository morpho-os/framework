<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\App\Web\View\JsonRenderer;
use Morpho\Base\Ok;
use Morpho\Testing\TestCase;

class JsonRendererTest extends TestCase {
    public function testCanRenderResult() {
        $result = new Ok(['foo' => 'bar']);

        $request = new class ($result) {
            public function __construct($result) {
                $this->response = new class ($result) extends ArrayObject {
                    private $body, $headers;

                    public function __construct($result) {
                        $this->headers = new \ArrayObject();
                        parent::__construct(['result' => $result]);
                    }

                    public function setBody($body) {
                        $this->body = $body;
                    }

                    public function headers() {
                        return $this->headers;
                    }

                    public function body() {
                        return $this->body;
                    }
                };
            }

            public function response() {
                return $this->response;
            }
        };
        $jsonRenderer = new JsonRenderer();

        $jsonRenderer($request);

        $response = $request->response();
        $rendered = $response->body();

        $this->assertJsonStringEqualsJsonString(json_encode($result), $rendered);
        $this->assertSame(['Content-Type' => 'application/json;charset=utf-8'], $response->headers()->getArrayCopy());
    }
}