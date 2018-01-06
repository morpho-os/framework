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
    public function testInvoke() {
        $response = new Response();
        $response->setStatusCode(Response::OK_STATUS_CODE);

        $data = ['foo' => 'bar'];
        $page = new Page('test', $data);
        $params = new \ArrayObject(['page' => $page]);

        $request = new Request($params);
        $request->setResponse($response);

        $renderer = new JsonRenderer();

        $renderer->__invoke($request);

        $this->assertSame(toJson($data), $response->body());
        $this->assertSame(['Content-Type' => 'application/json'], $response->headers()->getArrayCopy());
    }
}