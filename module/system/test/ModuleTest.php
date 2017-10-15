<?php
namespace MorphoTest\SystemTest;

use Monolog\Logger;
use Morpho\Base\Event;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\ConfigManager;
use Morpho\Web\ModulePathManager;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;
use Morpho\System\Module as SystemModule;
use Morpho\Web\Response;
use Morpho\Web\Site;

class ModuleTest extends TestCase {
    public function dataForDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        return [
            [
                new AccessDeniedException(), Response::STATUS_CODE_403,
            ],
            [
                new NotFoundException(), Response::STATUS_CODE_404,
            ],
            [
                new BadRequestException(), Response::STATUS_CODE_400,
            ],
            [
                new \RuntimeException(), Response::STATUS_CODE_500,
            ],
        ];
    }

    /**
     * @dataProvider dataForDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice
     */
    public function testDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice($exception, $expectedCode) {
        $handler = [
            "handler" => [
                "morpho-os/system",
                "SomeCtrl",
                "fooAction",
            ],
            'uri' => "/my/handler",
        ];
        $configManager = $this->newConfigManager($handler);

        $request = new Request();
        $request->isDispatched(true);

        $module = $this->newModule($configManager);

        $event = new Event('test', ['exception' => $exception, 'request' => $request]);
        $module->dispatchError($event);

        $this->assertFalse($request->isDispatched());
        $this->assertEquals($handler['handler'], $request->handler());
        $this->assertEquals($exception, $request->internalParam('error'));
        $this->assertEquals($expectedCode, $request->response()->getStatusCode());

        try {
            $module->dispatchError($event);
            $this->fail('Exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop has been detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $event->args['exception']);
        }
    }

    private function newConfigManager($valueToReturn): ConfigManager {
        return new class($valueToReturn) extends ConfigManager {
            private $value;

            public function __construct($value) {
                $this->value = $value;
            }

            public function getOrDefault(string $settingName, string $moduleName, $default = null) {
                $knownSettings = [
                    Request::ACCESS_DENIED_ERROR_HANDLER,
                    Request::BAD_REQUEST_ERROR_HANDLER,
                    Request::HOME_HANDLER,
                    Request::NOT_FOUND_ERROR_HANDLER,
                    Request::UNCAUGHT_ERROR_HANDLER,
                ];
                if (in_array($settingName, $knownSettings, true) && $moduleName === SystemModule::NAME) {
                    return $this->value;
                }
                if ($settingName === 'throwDispatchErrors') {
                    return false;
                }
                throw new \UnexpectedValueException();
            }
        };
    }

    private function newModule($configManager) {
        $module = new SystemModule('foo/bar', new ModulePathManager($this->getTestDirPath()));
        $serviceManager = new ServiceManager();
        $site = $this->createMock(Site::class);
        $site->method('config')
            ->willReturn([
                'modules' => [
                    SystemModule::NAME => [
                        'throwDispatchErrors' => false,
                    ],
                ],
            ]);
        $serviceManager->set('site', $site);
        $serviceManager->set('configManager', $configManager);
        $serviceManager->set('errorLogger', $this->createMock(Logger::class));
        $module->setServiceManager($serviceManager);
        return $module;
    }
}