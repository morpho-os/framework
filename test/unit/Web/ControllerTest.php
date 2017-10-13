<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Base\IFn;
use Morpho\Core\View;
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

            protected function trigger(string $event, array $args = null) {
                $this->triggerArgs = func_get_args();
                return '';
            }
        };
        $request = $this->newRequest();
        $request->setActionName('first');
        $controller->anotherView = $view;

        $controller->__invoke($request);

        $this->assertEquals(['render', ['view' => new View($viewName)]], $controller->triggerArgs);
    }

    public function testDispatch_Redirect() {
        $controller = new MyController('foo');
        $basePath = '/some/base/path';
        $request = $this->newRequest();
        $uri = new Uri();
        $uri->setBasePath($basePath);
        $request->setUri($uri);
        $request->setActionName('redirectToSomePage');

        $controller->__invoke($request);

        $this->assertTrue($request->isDispatched());
        $response = $request->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals("Location: $basePath/some/page", trim($response->headers()->toString()));
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
/*
        $router = $this->mock('\Morpho\Web\Routing\Router');
        $router->expects($this->once())
            ->method('assemble')
            ->with($this->equalTo($actionName), $this->equalTo($httpMethod), $this->equalTo($controllerName), $this->equalTo($moduleName), $this->equalTo(['foo' => 'bar']))
            ->will($this->returnValue("/$moduleName/$controllerName/$actionName/foo/bar"));
        $serviceManager->set('router', $router);
*/

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
    public function doRedirectToUri() {
        $this->redirectToUri('/system/module/list');
    }

    public function doForwardToAction($action, $controller, $module, $params) {
        $this->forwardToAction($action, $controller, $module, $params);
    }

    public function redirectToSomePageAction() {
        $this->redirectToUri('/some/page');
    }
}

class MyOtherController extends Controller {
    public $redirectArgs;

    public function doRedirectToAction(...$args) {
        parent::redirectToAction(...$args);
    }

    protected function redirectToUri(string $uri = null, int $httpStatusCode = null): void {
        $this->redirectArgs = func_get_args();
    }
}