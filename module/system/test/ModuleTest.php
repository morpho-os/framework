<?php
namespace MorphoTest\SystemTest;

use Monolog\Logger;
use Morpho\Core\SettingsManager;
use Morpho\Di\ServiceManager;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\ModuleFs;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;
use Morpho\Test\DbTestCase;
use Morpho\System\Module as SystemModule;
use Morpho\Web\Response;
use Morpho\Web\Site;

class ModuleTest extends DbTestCase {
    public function setUp() {
        parent::setUp();
        $db = $this->newDbConnection();
        $schemaManager = $db->schemaManager();
        $schemaManager->deleteAllTables();
        $schemaManager->createTables(SystemModule::tableDefinitions());
    }

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
        $settingsManager = $this->newSettingsManager($handler);

        $request = new Request();
        $request->isDispatched(true);

        $module = $this->newModule($settingsManager);

        $event = [
            null, // event name
            ['exception' => $exception, 'request' => $request] // args
        ];
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
            $this->assertEquals($e->getPrevious(), $event[1]['exception']);
        }
    }

    private function newSettingsManager($valueToReturn): SettingsManager {
        return new class($valueToReturn) extends SettingsManager {
            private $value;

            public function __construct($value) {
                $this->value = $value;
            }

            public function get(string $name, $moduleName) {
                $knownSettings = [
                    Request::ACCESS_DENIED_ERROR_HANDLER,
                    Request::BAD_REQUEST_ERROR_HANDLER,
                    Request::HOME_HANDLER,
                    Request::NOT_FOUND_ERROR_HANDLER,
                    Request::UNCAUGHT_ERROR_HANDLER,
                ];
                if (in_array($name, $knownSettings, true) && $moduleName === SystemModule::NAME) {
                    return $this->value;
                }
                throw new \UnexpectedValueException();
            }
        };
    }

    private function newModule($settingsManager) {
        $module = new SystemModule('foo/bar', new ModuleFs($this->getTestDirPath()));
        $serviceManager = new ServiceManager();
        $site = $this->createMock(Site::class);
        $site->method('config')
            ->willReturn([
                'throwDispatchErrors' => false,
            ]);
        $serviceManager->set('site', $site);
        $serviceManager->set('settingsManager', $settingsManager);
        $serviceManager->set('errorLogger', $this->createMock(Logger::class));
        $module->setServiceManager($serviceManager);
        return $module;
    }
}