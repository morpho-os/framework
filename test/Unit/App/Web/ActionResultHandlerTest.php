<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\Testing\TestCase;
use Morpho\App\Web\ActionResultHandler;
use Morpho\App\Web\Response;
use Morpho\App\Web\JsonResult;
use Morpho\App\Web\StatusCodeResult;
use Morpho\Ioc\IServiceManager;

class ActionResultHandlerTest extends TestCase {
    private $actionResultHandler;

    public function setUp(): void {
        parent::setUp();
        $serviceManager = $this->createMock(IServiceManager::class);
        $this->actionResultHandler = new ActionResultHandler($serviceManager);
    }

    public function testRedirect_Ajax_ReturnsJsonResult() {
        $redirectUri = 'http://localhost/foo/bar';
        $response = $this->mkResponse(['Location' => $redirectUri], true);
        $request = $this->mkRequest($response, true);

        ($this->actionResultHandler)($request);

        $this->assertSame($response, $request->response());
        $this->assertSame(Response::OK_STATUS_CODE, $response->statusCode());
        $this->checkJsonResult(['redirect' => $redirectUri], $response);
    }

    public function testRedirect_NotAjax() {
        $redirectUri = 'http://localhost/foo/bar';
        $response = $this->mkResponse(['Location' => $redirectUri], true);
        $request = $this->mkRequest($response, false);
        ($this->actionResultHandler)($request);

        $this->assertSame($response, $request->response());
        $this->assertTrue(!isset($response['result']));
    }

    public function dataForJsonResult() {
        yield [true];
        yield [false];
    }

    /**
     * @dataProvider dataForJsonResult_Ajax
     */
    public function testJsonResult($isAjax) {
        $response = $this->mkResponse([], false);
        $request = $this->mkRequest($response, $isAjax);

        $val = ['foo' => 'bar'];
        $jsonResult = new JsonResult($val);
        $response['result'] = $jsonResult;

        ($this->actionResultHandler)($request);

        $this->assertSame($response, $request->response);
        $this->checkJsonResult($val, $response);
    }

    private function mkRequest($response, bool $isAjax) {
        return new class ($response, $isAjax) {
            public function __construct($response, bool $isAjax) {
                $this->response = $response;
                $this->isAjax = $isAjax;
            }
            public function isAjax(): bool {
                return $this->isAjax;
            }

            public function response() {
                return $this->response;
            }
        };
    }

    private function mkResponse(array $headers, bool $isRedirect) {
        return new class ($headers, $isRedirect) extends \ArrayObject {
            private $statusCode;

            public function __construct(array $headers, bool $isRedirect) {
                $this->headers = new \ArrayObject($headers);
                $this->isRedirect = $isRedirect;
            }

            public function headers(): \ArrayObject {
                return $this->headers;
            }

            public function isRedirect(): bool {
                return $this->isRedirect;
            }

            public function setStatusCode($statusCode) {
                $this->statusCode = $statusCode;
            }

            public function statusCode() {
                return $this->statusCode;
            }

            public function setBody($body) {
                $this->body = $body;
            }

            public function body() {
                return $this->body;
            }
        };
    }

    private function checkJsonResult($expectedVal, $response) {
        $jsonResult = $response['result'];
        $this->assertInstanceOf(JsonResult::class, $jsonResult);
        $this->assertSame($expectedVal, $jsonResult->val());
        $this->assertMatchesRegularExpression('~application/json;\s*charset=utf-8~si', $response->headers()['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode($expectedVal), $response->body());
    }
}
