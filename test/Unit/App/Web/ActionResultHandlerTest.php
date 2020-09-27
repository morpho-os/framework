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
use Morpho\Ioc\IServiceManager;

class ActionResultHandlerTest extends TestCase {
    public function setupUp(): void {
        parent::setUp();
    }

    public function testRedirect_Ajax() {
        $serviceManager = $this->createMock(IServiceManager::class);
        $actionResultHandler = new ActionResultHandler($serviceManager);
        $redirectUri = 'http://localhost/foo/bar';
        $response = new class ($redirectUri) extends \ArrayObject {
            private $statusCode;
            public function __construct(string $redirectUri) {
                $this->headers = new \ArrayObject([
                    'Location' => $redirectUri,
                ]);
            }
            public function headers(): \ArrayObject {
                return $this->headers;
            }
            public function isRedirect(): bool {
                return true;
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
        $request = new class ($response) {
            public function __construct($response) {
                $this->response = $response;
            }
            public function isAjax(): bool {
                return true;
            }

            public function response() {
                return $this->response;
            }
        };
        $actionResultHandler($request);

        $this->assertSame($response, $request->response());
        $jsonResult = $response['result'];
        $this->assertInstanceOf(JsonResult::class, $jsonResult);
        $this->assertSame($redirectUri, $jsonResult->val()['redirect']);
        $this->assertSame(Response::OK_STATUS_CODE, $response->statusCode());
        $this->assertMatchesRegularExpression('~application/json;\s*charset=utf-8~si', $response->headers()['Content-Type']);
        $this->assertJsonStringEqualsJsonString(json_encode(['redirect' => $redirectUri]), $response->body());
    }
/*
    public function testRedirect_NotAjax() {

    }
 */
}
