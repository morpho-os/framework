<?php
namespace MorphoTest\Web;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\Request;

class ControllerTest extends TestCase {
    public function testRedirectToUri() {
        $controller = new MyController();
        $serviceManager = new ServiceManager();
        $request = new Request();
        $request->setBaseUri('');
        $response = $this->mock('\Morpho\Web\Response');
        $response->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo('/system/module/list'));
        $request->setResponse($response);
        $serviceManager->set('pathManager', function () use ($request) {
            return new \Morpho\Web\PathManager($request, $this->mock('\Morpho\Web\SiteManager'));
        });
        $controller->setServiceManager($serviceManager);

        $controller->setRequest($request);

        $controller->doRedirectToUri();
    }

    public function testForwardTo() {
        $controller = new MyController();
        $request = new Request();
        $controller->setRequest($request);
        $actionName = 'forward-here';
        $controllerName = 'my-other';
        $moduleName = 'morpho-test';
        $controller->doForwardToAction($actionName, $controllerName, $moduleName, ['p1' => 'v1']);
        $this->assertEquals($actionName, $request->getActionName());
        $this->assertEquals($controllerName, $request->getControllerName());
        $this->assertEquals($moduleName, $request->getModuleName());
        $this->assertEquals(['p1' => 'v1'], $request->getParams());
        $this->assertFalse($request->isDispatched());
    }

    public function testRedirectToAction() {
        $controller = new MyOtherController();

        $this->assertNull($controller->redirectArgs);
        $serviceManager = new ServiceManager();
        $actionName = 'redirect-here';
        $controllerName = 'my-some';
        $moduleName = 'morpho-test';
        $params = ['p1' => 'v1'];
        $httpMethod = Request::POST_METHOD;
        $serviceManager->set('router', function () use ($actionName, $httpMethod, $controllerName, $moduleName, $params) {
            $router = $this->mock('\Morpho\Web\Router');
            $router->expects($this->once())
                ->method('assemble')
                ->with($this->equalTo($actionName), $this->equalTo($httpMethod), $this->equalTo($controllerName), $this->equalTo($moduleName), $this->equalTo($params))
                ->will($this->returnValue("/$moduleName/$controllerName/$actionName/" . key($params) . '/' . current($params)));
            return $router;
        });
        $controller->setServiceManager($serviceManager);
        $controller->doRedirectToAction($actionName, $httpMethod, $controllerName, $moduleName, $params, ['foo' => 'bar']);

        $this->assertEquals(
            ["/$moduleName/$controllerName/$actionName/p1/v1", ['foo' => 'bar'], null],
            $controller->redirectArgs
        );
    }
}

class MyController extends Controller {
    public function doRedirectToUri() {
        $this->redirectToUri('/system/module/list');
    }

    public function doForwardToAction($action, $controller, $module, $params) {
        $this->forwardToAction($action, $controller, $module, $params);
    }
}

class MyOtherController extends Controller {
    public $redirectArgs;

    public function doRedirectToAction(...$args) {
        parent::redirectToAction(...$args);
    }

    protected function redirectToUri($uri = null, array $params = null, array $args = null, array $options = null, $code = null) {
        $this->redirectArgs = func_get_args();
    }
}

namespace MorphoTest\Web\App;

use Morpho\Base\Node;

class MyService extends Node {
}

namespace MorphoTest\Web\Domain;

use Morpho\Base\Node;

class MyService extends Node {
}
