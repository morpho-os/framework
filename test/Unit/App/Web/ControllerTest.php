<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\IRequest;
use Morpho\App\IResponse;
use Morpho\App\Web\View\JsonResult;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\HtmlResult;
use Morpho\Base\IFn;
use Morpho\Test\Unit\App\Web\ControllerTest\TMyController;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Controller;

require_once __DIR__ . '/_files/ControllerTest/TMyController.php';

class ControllerTest extends TestCase {
    /**
     * @var IFn
     */
    protected $controller;

    public function setUp(): void {
        parent::setUp();
        $this->controller = new MyController();
    }

    public function testInterface() {
        $this->assertInstanceOf(IFn::class, $this->controller);
    }

    public function testInvoke_ReturnNullFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler(['method' => 'returnNull']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnNull');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(HtmlResult::class, $actionResult);
        $this->assertSame('return-null', $actionResult->path());
        $this->assertSame([], $actionResult->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnArrayFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler(['method' => 'returnArray']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnArray');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(HtmlResult::class, $actionResult);
        $this->assertSame('return-array', $actionResult->path());
        $this->assertSame(['foo' => 'bar'], $actionResult->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnStringFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnString']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnString');
        $response = $request->response();
        $this->assertSame('returnStringCalled', $response->body());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnJsonFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnJson']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnJson');
        $response = $request->response();
        $this->assertEquals(new JsonResult('returnJsonCalled'), $response['result']);
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnHtmlFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnHtml']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnHtml');
        $response = $request->response();

        $view = $response['result'];

        $this->assertInstanceOf(HtmlResult::class, $view);
        $this->assertSame(['foo' => 'bar'], $view->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnResponseFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnResponse']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnResponse');
        $response = $request->response();
        $this->assertNotSame($response1, $response);
        $this->assertNull($response['result']);
    }

    public function testInvoke_ReturnRedirectFromAction() {
        $this->markTestIncomplete();
    }

    public function testSetParentActionResult() {
        $controller = new class extends Controller {
            public function beforeEach(): void {
                $this->setParentActionResult('some-page');
            }

            protected function doSomething() {
                return $this->mkHtmlResult();
            }
        };
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler(['method' => 'doSomething']);

        $controller->__invoke($request);

        $actionResult = $request->response()['result'];
        $this->assertSame('some-page', $actionResult->parent()->path());
        $this->assertSame('do-something', $actionResult->path());
    }

    protected function mkConfiguredRequest(array $serverVars = null): Request {
        $request = new Request(null, $serverVars);
        $response = new Response();
        $response->setBody('test');
        $request->setResponse($response);
        return $request;
    }

    protected function checkMethodCalled(string $method): void {
        $this->assertSame($method, $this->controller->calledMethod);
    }
}

class MyController extends Controller {
    use TMyController;
}
