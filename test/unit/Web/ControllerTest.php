<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\IRestResource;
use Morpho\Web\JsonResult;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\Uri\Uri;

class ControllerTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new Controller());
    }

    public function testInvoke_ReturningRestResource() {
        $controller = new MyController();

        $request = new Request();
        $this->configureUri($request);
        $request->isDispatched(true);
        $request->setActionName('returnActionResult');

        $controller->__invoke($request);

        $this->assertInstanceOf(IRestResource::class, $request->response()['result']);
    }

    public function testInvoke_ThrowsLogicExceptionIfRequestIsNotDispatched() {
        $controller = new MyController();
        $request = new Request();
        $request->isDispatched(false);
        $request->setActionName('returnArray');

        $this->expectException(\LogicException::class, 'Request must be dispatched');
        $controller->__invoke($request);
    }

    public function testInvoke_UnsetsResponseParamsFromPreviousInvoke() {
        $controller = new MyController();

        $request = new Request();
        $this->configureUri($request);
        $request->isDispatched(true);

        $request->setActionName('returnArray');
        $request->response()['foo'] = 'test';
        $controller->__invoke($request);
        $this->assertFalse(isset($request->response()['foo']));
        $this->assertTrue(isset($request->response()['result']));

        $request->response()['bar'] = 'test';
        $request->setActionName('redirectNoArgs');
        $controller->__invoke($request);
        $this->assertFalse(isset($request->response()['bar']));
        $this->assertFalse(isset($request->response()['result']));
    }

    public function dataForInvoke_Redirect_Ajax() {
        $noopFn = function () {};
        $returnResponseFn = function ($controller) {
            $controller->returnResponse = new Response();
        };
        // {redirect/1, not-redirect/0} x {ajax/1, not-ajax/0}
        yield [
            'redirectNoArgs', // 0
            false,            // 0
            false,
            $noopFn,
        ];
        yield [
            'redirectNoArgs', // 0
            true,             // 1
            true,
            $noopFn,
        ];
        yield [
            'returnResponse', // 1
            false,            // 0
            false,
            $returnResponseFn,
        ];
        yield [
            'returnResponse', // 1
            true,             // 1
            true,
            $returnResponseFn,
        ];
    }

    /**
     * @dataProvider dataForInvoke_Redirect_Ajax
     */
    public function testInvoke_Redirect_Ajax($actionName, bool $isAjax, bool $hasPage, callable $configureController) {
        $controller = new MyController();
        $configureController($controller);

        $request = new Request();
        $request->setActionName($actionName);
        $request->isAjax($isAjax);
        $request->isDispatched(true);
        $this->configureUri($request);

        $controller->__invoke($request);

        $this->assertSame($hasPage, isset($request->response()['result']));
    }

    public function dataRedirect_HasArgs() {
        yield [300];
        yield [301];
        yield [302];
    }

    /**
     * @dataProvider dataRedirect_HasArgs
     */
    public function testRedirect_HasArguments($statusCode) {
        $controller = new MyController();
        $controller->statusCode = $statusCode;
        $request = $this->newRequest();
        $uri = new Uri('http://localhost/base/path/some/module?foo=bar');
        $basePath = '/base/path';
        $uri->path()->setBasePath($basePath);
        $request->setUri($uri);
        $request->setActionName('redirectHasArgs');

        $controller->__invoke($request);

        $this->assertTrue($request->isDispatched());
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(
            ['Location' => "$basePath/some/page"],
            $response->headers()->getArrayCopy()
        );
        $this->assertSame($statusCode, $response->statusCode());
        $this->assertTrue(!isset($request['result']));
    }

    public function testRedirect_NoArgs() {
        $controller = new MyController();
        $request = $this->newRequest();
        $uriStr = 'http://localhost/base/path/some/module?foo=bar';
        $request->setUri(new Uri($uriStr));
        $request->setActionName('redirectNoArgs');

        $controller->__invoke($request);

        $this->assertTrue($request->isDispatched());
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(
            ['Location' => $uriStr],
            $response->headers()->getArrayCopy()
        );
        $this->assertSame(302, $response->statusCode());
        $this->assertTrue(!isset($request['result']));
    }

    public function testForwardTo() {
        $controller = new MyController();
        $request = $this->newRequest();

        $actionName = 'forward-here';
        $controllerName = 'my-other';
        $moduleName = 'morpho-test';
        $request->setActionName('forward');
        $controller->forwardTo = [$actionName, $controllerName, $moduleName, ['p1' => 'v1']];
        
        $controller->__invoke($request);
        
        $this->assertEquals($actionName, $request->actionName());
        $this->assertEquals($controllerName, $request->controllerName());
        $this->assertEquals($moduleName, $request->moduleName());
        $this->assertEquals(['p1' => 'v1'], $request['routing']);
        $this->assertFalse($request->isDispatched());
        $this->assertTrue(!isset($request['result']));
    }

    public function testInvoke_ReturningResponseFromAction() {
        $controller = new MyController();
        $controller->returnResponse = $response = new Response();
        $response->setBody('foo');
        $request = $this->newRequest();
        $request->setActionName('returnResponse');

        $controller->__invoke($request);

        $this->assertSame($response, $request->response());
        $this->assertTrue(!isset($request['result']));
        $this->assertSame('foo', $response->body());
    }
    
    public function testInvoke_ReturningArrayFromAction() {
        $controller = new MyController();
        $request = $this->newRequest();
        $request->setActionName('returnArray');

        $controller->__invoke($request);

        $page = $request->response()['result'];
        $this->assertSame(['foo' => 'bar'], $page->getArrayCopy());
        $this->assertSame('', $request->response()->body());
    }

    private function newRequest(array $serverVars = null) {
        $request = new Request(null, $serverVars, new class implements IFn { public function __invoke($value) {} });
        $request->isDispatched(true);
        return $request;
    }

    private function configureUri(Request $request): void {
        $uri = new Uri('http://localhost/base/path/some/module?foo=bar');
        $basePath = '/base/path';
        $uri->path()->setBasePath($basePath);
        $request->setUri($uri);
    }
}

class MyController extends Controller {
    public $statusCode;
    public $forwardTo;
    public $returnResponse;

    public function forwardAction() {
        $this->forward(...$this->forwardTo);
    }

    public function redirectHasArgsAction() {
        $this->redirect('/some/page', $this->statusCode);
    }

    public function redirectNoArgsAction() {
        $this->redirect();
    }

    public function returnResponseAction() {
        return $this->returnResponse;
    }

    public function returnArrayAction() {
        return ['foo' => 'bar'];
    }

    public function returnActionResultAction() {
        return new JsonResult(['foo' => 'bar']);
    }
}