<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Base\Event;
use Morpho\Base\IFn;
use Morpho\Web\View\View;
use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\Request;
use Morpho\Di\ServiceManager;
use Morpho\Web\Uri;

class ControllerTest extends TestCase {
    public function testInterfaces() {
        $this->assertInstanceOf(IFn::class, new Controller('foo'));
    }

    public function dataForDispatch_SetDifferentViewFromAction() {
        return [
            [
                'list', 'list',
            ],
            [
                'create', new View('create'),
            ],
        ];
    }

    /**
     * @dataProvider dataForDispatch_SetDifferentViewFromAction
     */
    public function testInvoke_SetDifferentViewFromAction($viewName, $view) {
        $controller = new class('foo') extends Controller {
            public $triggerArgs;

            protected function firstAction() {
                $this->setView($this->anotherView);
            }

            protected function trigger(Event $event) {
                $this->triggerArgs = func_get_args();
                return '';
            }
        };
        $request = $this->newRequest();
        $request->setActionName('first');
        $controller->anotherView = $view;

        $controller->__invoke($request);

        $this->assertEquals([new Event('render', ['view' => new View($viewName)])], $controller->triggerArgs);
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
        $request->setUri(new Uri('http://localhost/base/path/some/module?foo=bar'));
        $request->setActionName('redirectHasArgs');

        $controller->__invoke($request);

        $this->assertTrue($request->isDispatched());
        /** @var \Morpho\Web\Response $response */
        $response = $request->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(
            ['Location' => "/some/page"],
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
        $this->assertEquals(['p1' => 'v1'], $request->routingParams());
        $this->assertFalse($request->isDispatched());
    }

    public function testRedirectToAction() {
        $this->markTestIncomplete();

        $controller = new MyOtherController('foo');

        $serviceManager = new ServiceManager();

        $this->assertNull($controller->redirectArgs);
        $actionName = 'redirect-here';
        $controllerName = 'my-some';
        $moduleName = 'morpho-test';
        $httpMethod = Request::POST_METHOD;

        $controller->setServiceManager($serviceManager);

        $controller->doRedirectToAction($actionName, $httpMethod, $controllerName, $moduleName, ['foo' => 'bar']);

        $this->assertEquals(
            ["/$moduleName/$controllerName/$actionName/foo/bar"],
            $controller->redirectArgs
        );
    }

    private function newRequest() {
        $request = new Request();
        $request->isDispatched(true);
        return $request;
    }
}

class MyController extends Controller {
    public $statusCode;

    public function doForwardToAction($action, $controller, $module, $params) {
        $this->forwardToAction($action, $controller, $module, $params);
    }

    public function redirectHasArgsAction() {
        $this->redirect('/some/page', $this->statusCode);
    }

    public function redirectNoArgsAction() {
        $this->redirect();
    }
}

class MyOtherController extends Controller {
    public $redirectArgs;

    public function doRedirectToAction(...$args) {
        parent::redirectToAction(...$args);
    }

    protected function redirect($uri = null, int $httpStatusCode = null): void {
        $this->redirectArgs = func_get_args();
    }
}