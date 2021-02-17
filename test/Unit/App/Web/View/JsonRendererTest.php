<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\App\IRequest;
use Morpho\App\IResponse;
use Morpho\App\Web\View\JsonRenderer;
use Morpho\Base\Ok;
use Morpho\Testing\TestCase;

class JsonRendererTest extends TestCase {
    public function testCanRenderResult() {
        $result = new Ok(['foo' => 'bar']);

        $request = new class ($result) implements IRequest {
            public function __construct($result) {
                $this->response = new class ($result) extends ArrayObject implements IResponse {
                    private $body, $headers;

                    public function __construct($result) {
                        $this->headers = new ArrayObject();
                        parent::__construct(['result' => $result]);
                    }

                    public function setBody(string $body): void {
                        $this->body = $body;
                    }

                    public function headers() {
                        return $this->headers;
                    }

                    public function body(): string {
                        return $this->body;
                    }

                    public function isBodyEmpty(): bool {
                        // TODO: Implement isBodyEmpty() method.
                    }

                    public function send(): void {
                        // TODO: Implement send() method.
                    }

                    public function setStatusCode(int $statusCode): void {
                        // TODO: Implement setStatusCode() method.
                    }

                    public function statusCode(): int {
                        // TODO: Implement statusCode() method.
                    }

                    public function resetState(): void {
                        // TODO: Implement resetState() method.
                    }
                };
            }

            public function response(): IResponse {
                return $this->response;
            }

            public function getIterator() {
                // TODO: Implement getIterator() method.
            }

            public function offsetExists($offset) {
                // TODO: Implement offsetExists() method.
            }

            public function offsetGet($offset) {
                // TODO: Implement offsetGet() method.
            }

            public function offsetSet($offset, $value) {
                // TODO: Implement offsetSet() method.
            }

            public function offsetUnset($offset) {
                // TODO: Implement offsetUnset() method.
            }

            public function serialize() {
                // TODO: Implement serialize() method.
            }

            public function unserialize($serialized) {
                // TODO: Implement unserialize() method.
            }

            public function count() {
                // TODO: Implement count() method.
            }

            public function exchangeArray(object|array $arr) {
                // TODO: Implement exchangeArray() method.
            }

            public function isHandled(bool $flag = null): bool {
                // TODO: Implement isHandled() method.
            }

            public function setHandler(array $handler): void {
                // TODO: Implement setHandler() method.
            }

            public function handler(): array {
                // TODO: Implement handler() method.
            }

            public function setResponse(IResponse $response): void {
                // TODO: Implement setResponse() method.
            }

            public function args($namesOrIndexes = null) {
                // TODO: Implement args() method.
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