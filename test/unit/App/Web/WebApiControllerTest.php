<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Controller;
use Morpho\App\Web\Json;
use Morpho\App\Web\WebApiController;
use Morpho\Test\Unit\App\Web\ControllerTest\TMyController;

require_once __DIR__ . '/_files/ControllerTest/TMyController.php';

class WebApiControllerTest extends ControllerTest {
    /**
     * @var WebApiController
     */
    protected $controller;

    public function setUp() {
        parent::setUp();
        $this->controller = new MyWebApiController();
    }

    public function testInterface() {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testInvoke_ReturnNullFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setActionName('returnNull');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnNullAction');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(Json::class, $actionResult);
        $this->assertSame([], $actionResult->value());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnArrayFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setActionName('returnArray');
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnArrayAction');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(Json::class, $actionResult);
        $this->assertSame(['foo' => 'bar'], $actionResult->value());
        $this->assertSame($response1, $response);
    }
}

class MyWebApiController extends WebApiController {
    use TMyController;
}
