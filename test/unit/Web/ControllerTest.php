<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\Uri\Uri;

class ControllerTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new Controller());
    }

    public function dataForRedirect_HasArgs() {
        yield [300];
        yield [301];
        yield [302];
    }

    /**
     * @dataProvider dataForRedirect_HasArgs
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
        $this->assertTrue(!isset($request->params()['page']));
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
        $this->assertTrue(!isset($request->params()['page']));
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
        $this->assertEquals(['p1' => 'v1'], $request->params()['routing']);
        $this->assertFalse($request->isDispatched());
        $this->assertTrue(!isset($request->params()['page']));
    }

    public function testReturningResponseFromAction() {
        $controller = new MyController();
        $controller->returnResponse = $response = new Response();
        $response->setBody('foo');
        $request = $this->newRequest();
        $request->setActionName('returnResponse');

        $controller->__invoke($request);

        $this->assertSame($response, $request->response());
        $this->assertTrue(!isset($request->params()['page']));
        $this->assertSame('foo', $response->body());
    }
    
    public function testReturningArrayFromAction() {
        $controller = new MyController();
        $request = $this->newRequest();
        $request->setActionName('returnArray');

        $controller->__invoke($request);

        $page = $request->params()['page'];
        $this->assertSame(['foo' => 'bar'], $page->vars()->getArrayCopy());
        $this->assertSame('', $request->response()->body());
    }

    private function newRequest(array $serverVars = null) {
        $request = new Request($serverVars, new class implements IFn { public function __invoke($value) {} });
        $request->isDispatched(true);
        return $request;
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
}