<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\IResponse;
use Morpho\App\Web\View\JsonResult;
use Morpho\App\Web\View\HtmlResult;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\JsonRenderer;

class JsonRendererTest extends TestCase {
    public function testInvoke_HtmlResult() {
        $request = new Request();

        $data = ['foo' => 'bar'];
        $actionResult = new HtmlResult('test', $data);
        $response = new Response(['result' => $actionResult]);

        $statusCode = 201;
        $response->setStatusCode($statusCode);
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        $this->assertJsonStringEqualsJsonString(json_encode($actionResult), $response->body());
        $this->assertSame(['Content-Type' => 'application/json;charset=utf-8'], $response->headers()->getArrayCopy());
        $this->assertSame($statusCode, $response->statusCode());
    }

    public function testInvoke_JsonActionResult() {
        $renderer = new JsonRenderer();

        $request = new Request();
        $response = $request->response();
        $statusCode = 201;
        $response->setStatusCode($statusCode);
        $data = [
            ['foo' => 'bar']
        ];
        $request->response()['result'] = new JsonResult($data);

        $renderer->__invoke($request);

        $this->checkJsonResponse($statusCode, $response, $data);
    }

    private function checkJsonResponse(int $expectedStatusCode, IResponse $response, $data) {
        $this->assertJsonStringEqualsJsonString(json_encode($data), $response->body());
        $this->assertSame(['Content-Type' => 'application/json;charset=utf-8'], $response->headers()->getArrayCopy());
        $this->assertSame($expectedStatusCode, $response->statusCode());
    }
}
