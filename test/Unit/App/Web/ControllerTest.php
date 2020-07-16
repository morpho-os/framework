<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\IRequest;
use Morpho\App\IResponse;
use Morpho\App\Web\JsonResult;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;
use Morpho\App\Web\View\ViewResult;
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
        $request->setHandler(['method' => 'returnNullAction', 'action' => 'return-null']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnNullAction');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(ViewResult::class, $actionResult);
        $this->assertSame('return-null', $actionResult->path());
        $this->assertSame([], $actionResult->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }
    
    public function testInvoke_ReturnArrayFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler(['method' => 'returnArrayAction', 'action' => 'return-array']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnArrayAction');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(ViewResult::class, $actionResult);
        $this->assertSame('return-array', $actionResult->path());
        $this->assertSame(['foo' => 'bar'], $actionResult->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnStringFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnStringAction', 'action' => 'return-string']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnStringAction');
        $response = $request->response();
        $this->assertSame('returnStringActionCalled', $response->body());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnJsonFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnJsonAction', 'action' => 'return-json']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnJsonAction');
        $response = $request->response();
        $this->assertEquals(new JsonResult('returnJsonActionCalled'), $response['result']);
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnViewFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnViewAction', 'action' => 'return-view']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnViewAction');
        $response = $request->response();

        $view = $response['result'];

        $this->assertInstanceOf(ViewResult::class, $view);
        $this->assertSame(['foo' => 'bar'], $view->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnResponseFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setHandler(['method' => 'returnResponseAction']);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnResponseAction');
        $response = $request->response();
        $this->assertNotSame($response1, $response);
        $this->assertNull($response['result']);
    }

    public function testInvoke_ReturnRedirectFromAction() {
        $this->markTestIncomplete();
    }

    public function testInvoke_CallsResetStateOfResponse() {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $response->expects($this->once())
            ->method('resetState');
        $request->expects($this->any())
            ->method('response')
            ->willReturn($response);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn(['method' => 'returnNullAction', 'action' => 'return-null']);

        $this->controller->__invoke($request);
    }

    public function testSetParentViewResult() {
        $controller = new class extends Controller {
            public function beforeEach(): void {
                $this->setParentViewResult('some-page');
            }

            protected function doSomethingAction() {
                return $this->mkViewResult();
            }
        };
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler(['method' => 'doSomethingAction', 'action' => 'do-something']);

        $controller->__invoke($request);

        $viewResult = $request->response()['result'];
        $this->assertSame('some-page', $viewResult->parent()->path());
        $this->assertSame('do-something', $viewResult->path());
    }

    protected function mkConfiguredRequest(array $serverVars = null): Request {
        $uriChecker = new class implements IFn { public function __invoke($value) {} };
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
