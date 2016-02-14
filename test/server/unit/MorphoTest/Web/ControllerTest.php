<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Controller;
use Morpho\Web\Request;
use Morpho\Di\ServiceManager;
use Morpho\Web\SiteManager;
use Morpho\Web\Uri;

class ControllerTest extends TestCase {
    public function testRedirectToUri() {
        $controller = new MyController();
        $request = new Request();
        $uri = new Uri();
        $uri->setBasePath('/some/base/path');
        $request->setUri($uri);
        $response = $this->mock('\Morpho\Web\Response');
        $response->expects($this->once())
            ->method('redirect')
            ->with($this->equalTo('/some/base/path/system/module/list'));
        $request->setResponse($response);
        $serviceManager = new ServiceManager(null, ['siteManager' => new SiteManager()]);
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
        $this->markTestIncomplete();

        $controller = new MyOtherController();

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

    protected function redirectToUri(string $uri = null, int $httpStatusCode = null) {
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
