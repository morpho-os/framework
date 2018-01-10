<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
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
        $params = new \ArrayObject(['page' => $page]);
        $response = new Response($params);
        $statusCode = Response::OK_STATUS_CODE;
        $response->setStatusCode($statusCode);
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        $this->assertSame(toJson($page), $response->body());
        $this->assertSame(['Content-Type' => 'application/json'], $response->headers()->getArrayCopy());
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
        $params = new \ArrayObject(['page' => $page]);
        $response = new Response($params);
        if ($redirectUriStr) {
            $response->redirect($redirectUriStr, $statusCode);
        }
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        if ($redirectUriStr) {
            $expectedBody['redirect'] = $redirectUriStr;
        }
        $this->assertSame(toJson($page), $response->body());
        $this->assertSame(['Content-Type' => 'application/json'], $response->headers()->getArrayCopy());
        $this->assertSame(Response::OK_STATUS_CODE, $response->statusCode());
    }
}