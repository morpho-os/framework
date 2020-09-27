<?php declare(strict_types=1);
namespace Morpho\Test\Unit\App\Web;

use Morpho\App\Web\Controller;
use Morpho\App\Web\JsonResult;
use Morpho\App\Web\ApiController;
use Morpho\Test\Unit\App\Web\ControllerTest\TMyController;

require_once __DIR__ . '/_files/ControllerTest/TMyController.php';

class ApiControllerTest extends ControllerTest {
    /**
     * @var ApiController
     */
    protected $controller;

    public function setUp(): void {
        parent::setUp();
        $this->controller = new MyApiController();
    }

    public function testInterface() {
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testInvoke_ReturnNullFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler([
            'method' => 'returnNull',
        ]);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnNull');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(JsonResult::class, $actionResult);
        $this->assertSame([], $actionResult->val());
        $this->assertSame($response1, $response);
    }

    public function testInvoke_ReturnArrayFromAction() {
        $request = $this->mkConfiguredRequest(null);
        $request->setHandler([
            'method' => 'returnArray',
        ]);
        $response1 = $request->response();

        $this->controller->__invoke($request);

        $this->checkMethodCalled('returnArray');
        $response = $request->response();
        $actionResult = $response['result'];
        $this->assertInstanceOf(JsonResult::class, $actionResult);
        $this->assertSame(['foo' => 'bar'], $actionResult->val());
        $this->assertSame($response1, $response);
    }
}

class MyApiController extends ApiController {
    use TMyController;
}
