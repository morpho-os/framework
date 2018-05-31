<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Json;
use Morpho\App\Web\View\View;
use Morpho\Base\IFn;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Controller;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;

class ControllerTest extends TestCase {
    /**
     * @var IFn
     */
    private $controller;

    public function setUp() {
        parent::setUp();
        $this->controller = new MyController(['checkActionMethodExistence' => false]);
    }

    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new Controller());
    }

    public function testInvoke_ReturnNullFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setActionName('returnNull');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnNullAction');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(View::class, $actionResult);
        $this->assertSame('return-null', $actionResult->name());
        $this->assertSame([], $actionResult->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnStringFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setActionName('returnString');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnStringAction');
        $response = $request->response();
        $this->assertSame(MyController::class . '::returnStringActionCalled', $response->body());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnJsonFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setActionName('returnJson');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnJsonAction');
        $response = $request->response();
        $this->assertEquals(new Json(MyController::class . '::returnJsonActionCalled'), $response['result']);
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnViewFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setActionName('returnView');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnViewAction');
        $response = $request->response();

        $view = $response['result'];

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(['foo' => 'bar'], $view->vars()->getArrayCopy());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnResponseFromAction() {
        $request = $this->mkConfiguredRequest();
        $request->setActionName('returnResponse');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnResponseAction');
        $response = $request->response();
        $this->assertNotSame($response1, $response);
        $this->assertTrue(!isset($response['result']));
    }

    public function testInvoke_ReturnRedirectFromAction() {
        $this->markTestIncomplete();
    }
/*    public function testInvoke_UnsetsResponseParamsFromPreviousInvoke() {
        $controller = new MyController();

        $request = new Request();
        $this->configureUri($request);

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
    }*/
/*
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
*/
    /**
     * @dataProvider dataForInvoke_Redirect_Ajax

    public function testInvoke_Redirect_Ajax($actionName, bool $isAjax, bool $hasPage, callable $configureController) {
        $controller = new MyController();
        $configureController($controller);

        $request = new Request();
        $request->setActionName($actionName);
        $this->configureUri($request);

        $controller->__invoke($request);

        $this->assertSame($hasPage, isset($request->response()['result']));
    }
     */
    public function dataRedirect_HasArgs() {
        yield [300];
        yield [301];
        yield [302];
    }



    /**
     * @dataProvider dataRedirect_HasArgs

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

        $this->assertTrue($request->isHandled());
        /** @var \Morpho\App\Web\Response $response * /
        $response = $request->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(
            ['Location' => "$basePath/some/page"],
            $response->headers()->getArrayCopy()
        );
        $this->assertSame($statusCode, $response->statusCode());
        $this->assertTrue(!isset($request['result']));
    }
     *      */
/*
    public function testRedirect_NoArgs() {
        $controller = new MyController();
        $request = $this->newRequest();
        $uriStr = 'http://localhost/base/path/some/module?foo=bar';
        $request->setUri(new Uri($uriStr));
        $request->setActionName('redirectNoArgs');

        $controller->__invoke($request);

        $this->assertTrue($request->isHandled());
        /** @var \Morpho\App\Web\Response $response * /
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
        $this->assertFalse($request->isHandled());
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
*/

/*    private function configureUri(Request $request): void {
        $uri = new Uri('http://localhost/base/path/some/module?foo=bar');
        $basePath = '/base/path';
        $uri->path()->setBasePath($basePath);
        $request->setUri($uri);
    }*/

    private function mkConfiguredRequest(array $serverVars = null): Request {
        $uriChecker = new class implements IFn { public function __invoke($value) {} };
        $request = new Request(null, $serverVars, $uriChecker);
        $response = new Response();
        $response->setBody('test');
        $request->setResponse($response);
        return $request;
    }

    private function checkMethodCalled(string $method): void {
        $this->assertSame(MyController::class . '::' . $method, $this->controller->calledMethod);
    }
}

class MyController extends Controller {
    public $calledMethod;

    public function returnNullAction() {
        $this->calledMethod = __METHOD__;
        return null;
    }

    public function returnStringAction() {
        $this->calledMethod = __METHOD__;
        return __METHOD__ . 'Called';
    }

    public function returnJsonAction() {
        $this->calledMethod = __METHOD__;
        return $this->mkJson(__METHOD__ . 'Called');
    }

    public function returnResponseAction() {
        $this->calledMethod = __METHOD__;
        return $this->mkResponse(null, __METHOD__ . 'Called');
    }

    public function returnViewAction() {
        $this->calledMethod = __METHOD__;
        return $this->mkView('test', ['foo' => 'bar']);
    }

    /*    public $statusCode;
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
*/
}
