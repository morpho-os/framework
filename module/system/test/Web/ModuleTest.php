<?php
namespace MorphoTest\System\Web;

use Monolog\Logger;
use Morpho\Base\Event;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\ModuleIndex;
use Morpho\Web\ModuleMeta;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;
use Morpho\System\Web\Module as SystemModule;
use Morpho\Web\Response;

class ModuleTest extends TestCase {
    public function dataForDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        return [
            [
                new AccessDeniedException(), Response::STATUS_CODE_403, false,
            ],
            [
                new NotFoundException(), Response::STATUS_CODE_404, false,
            ],
            [
                new BadRequestException(), Response::STATUS_CODE_400, false,
            ],
            [
                new \RuntimeException(), Response::STATUS_CODE_500, true,
            ],
        ];
    }

    /**
     * @dataProvider dataForDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice
     */
    public function testDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice($exception, $expectedCode, $mustLogError) {
        $handler = [
            "handler" => [
                "morpho-os/system",
                "SomeCtrl",
                "fooAction",
            ],
            'uri' => "/my/handler",
        ];
        $config = new \ArrayObject([
            'throwDispatchErrors' => false,
            Request::ACCESS_DENIED_ERROR_HANDLER => $handler,
            Request::BAD_REQUEST_ERROR_HANDLER => $handler,
            Request::HOME_HANDLER => $handler,
            Request::NOT_FOUND_ERROR_HANDLER => $handler,
            Request::UNCAUGHT_ERROR_HANDLER => $handler,
        ]);

        $request = new Request();
        $request->isDispatched(true);

        $module = $this->newModule($config, $exception, $mustLogError);

        $event = new Event('test', ['exception' => $exception, 'request' => $request]);
        $module->dispatchError($event);

        $this->assertFalse($request->isDispatched());
        $this->assertEquals($handler['handler'], $request->handler());
        $this->assertEquals($exception, $request->params()['error']);
        $this->assertEquals($expectedCode, $request->response()->getStatusCode());

        try {
            $module->dispatchError($event);
            $this->fail('Exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop has been detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $event->args['exception']);
        }
    }

    private function newModule($config, $exception, $mustLogError) {
        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleName = 'morpho-os/system';
        $moduleIndex->expects($this->any())
            ->method('moduleMeta')
            ->with($moduleName)
            ->will($this->returnValue(new ModuleMeta($moduleName, $config)));
        $module = new SystemModule($moduleName, $moduleIndex);
        $serviceManager = $this->createMock(ServiceManager::class);
        $errorLogger = $this->createMock(Logger::class);
        if ($mustLogError) {
            $errorLogger->expects($this->exactly(2))
                ->method('emergency')
                ->with($this->equalTo($exception), $this->equalTo(['exception' => $exception]));
        } else {
            $errorLogger->expects($this->never())
                ->method('emergency');
        }
        $serviceManager->expects($this->any())
            ->method('get')
            ->with('errorLogger')
            ->willReturn($errorLogger);
        $module->setServiceManager($serviceManager);
        return $module;
    }
}