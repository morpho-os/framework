<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\JsonResult;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\View\JsonRenderer;
use function Morpho\Base\toJson;
use Morpho\Web\View\Page;

class JsonRendererTest extends TestCase {
    public function testInvoke_NonAjax() {
        $request = new Request();

        $data = ['foo' => 'bar'];
        $page = new Page('test', $data);
        $response = new Response(['result' => $page]);
        $statusCode = Response::OK_STATUS_CODE;
        $response->setStatusCode($statusCode);
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        $this->assertSame(toJson($page), $response->body());
        $this->assertSame(['Content-Type' => 'application/json;charset=utf-8'], $response->headers()->getArrayCopy());
    }

    public function dataForInvoke_Ajax() {
        yield [
            '/foo/bar',
        ];
        yield [
            null,
        ];
    }

    /**
     * @dataProvider dataForInvoke_Ajax
     */
    public function testInvoke_Ajax(?string $redirectUriStr) {
        $request = new Request();
        $request->isAjax(true);

        $statusCode = $redirectUriStr ? Response::FOUND_STATUS_CODE : Response::OK_STATUS_CODE;

        $data = ['foo' => 'bar'];
        $page = new Page('test', $data);
        $response = new Response(['result' => $page]);
        if ($redirectUriStr) {
            $response->redirect($redirectUriStr, $statusCode);
        }
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        $expectedBody = $data;

        if ($redirectUriStr) {
            $expectedBody['redirect'] = $redirectUriStr;
        }

        $this->checkJsonResponse($response, $expectedBody);
    }

    public function testInvoke_RenderJsonActionResult() {
        $renderer = new JsonRenderer();

        $request = new Request();
        $data = [
            ['foo' => 'bar']
        ];
        $request->response()['result'] = new JsonResult($data);

        $renderer->__invoke($request);

        $this->checkJsonResponse($request->response(), $data);
    }

    private function checkJsonResponse($response, $data) {
        /** @var Response $response */
        $this->assertSame(toJson($data), $response->body());
        $this->assertSame(['Content-Type' => 'application/json;charset=utf-8'], $response->headers()->getArrayCopy());
        $this->assertSame(Response::OK_STATUS_CODE, $response->statusCode());
    }
}