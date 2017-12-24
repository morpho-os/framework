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
use Morpho\Web\Uri\Uri;

class ControllerTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new Controller('foo'));
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
        $controller = new MyController('foo');
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
    }

    public function testRedirect_NoArgs() {
        $controller = new MyController('foo');
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
    }

    public function testForwardTo() {
        $controller = new MyController('foo');
        $request = $this->newRequest();
        $controller->setRequest($request);
        $actionName = 'forward-here';
        $controllerName = 'my-other';
        $moduleName = 'morpho-test';
        
        $controller->doForwardToAction($actionName, $controllerName, $moduleName, ['p1' => 'v1']);
        
        $this->assertEquals($actionName, $request->actionName());
        $this->assertEquals($controllerName, $request->controllerName());
        $this->assertEquals($moduleName, $request->moduleName());
        $this->assertEquals(['p1' => 'v1'], $request->params()['routing']);
        $this->assertFalse($request->isDispatched());
    }

    private function newRequest(array $serverVars = null) {
        $request = new Request($serverVars, new class implements IFn { public function __invoke($value) {} });
        $request->isDispatched(true);
        return $request;
    }
}

class MyController extends Controller {
    public $statusCode;

    public function doForwardToAction($action, $controller, $module, $params) {
        $this->forward($action, $controller, $module, $params);
    }

    public function redirectHasArgsAction() {
        $this->redirect('/some/page', $this->statusCode);
    }

    public function redirectNoArgsAction() {
        $this->redirect();
    }
}