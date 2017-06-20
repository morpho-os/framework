<?php declare(strict_types=1);
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\Request;
use Morpho\Di\ServiceManager;
use Morpho\Web\Uri;

class ControllerTest extends TestCase {
    public function testDispatch_Redirect() {
        $controller = new MyController('foo');
        $basePath = '/some/base/path';
        $request = $this->newRequest($basePath);
        $uri = new Uri();
        $uri->setBasePath($basePath);
        $request->setUri($uri);
        $request->setActionName('redirectToSomePage');

        $controller->dispatch($request);

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
        return $this->redirectToUri('/some/page');
    }
}

class MyOtherController extends Controller {
    public $redirectArgs;

    public function doRedirectToAction(...$args) {
        parent::redirectToAction(...$args);
    }

    protected function redirectToUri(string $uri = null, int $httpStatusCode = null) {
        $this->redirectArgs = func_get_args();
    }
}