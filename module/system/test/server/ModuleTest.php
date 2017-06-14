<?php
namespace MorphoTest\SystemTest;

use Morpho\Core\SettingsManager;
use Morpho\Di\ServiceManager;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\Request;
use Morpho\Test\DbTestCase;
use Morpho\Web\Response;
use Morpho\System\Module as SystemModule;
use Morpho\Web\Site;

class ModuleTest extends DbTestCase {
    public function setUp() {
        parent::setUp();
        $db = $this->db();
        $schemaManager = $db->schemaManager();
        $schemaManager->deleteAllTables();
        $schemaManager->createTables(SystemModule::tableDefinitions());
    }

    public function testDispatchError_SetsDefaultHandlerForAccessDenied() {
        $settingsManager = $this->newSettingsManager(false);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingsManager);

        $module->dispatchError($event);

        $this->assertRequestHasHandlerAndException(
            $request,
            SystemModule::defaultErrorHandler(SystemModule::ACCESS_DENIED_ERROR),
            $exception
        );
    }

    public function testDispatchError_SetsUserDefinedHandlerIfSetForAccessDenied() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingsManager = $this->newSettingsManager($handler);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingsManager);

        $module->dispatchError($event);

        $this->assertRequestHasHandlerAndException($request, $handler, $exception);
    }

    public function testDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingsManager = $this->newSettingsManager($handler);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingsManager);

        $module->dispatchError($event);

        $this->assertRequestHasHandlerAndException($request, $handler, $exception);

        $event[1]['exception'] = $this->throwAccessDenied();
        try {
            $module->dispatchError($event);
            $this->fail();
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop has been detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $event[1]['exception']);
        }
    }

    private function initModuleForDispatchError(\Exception $exception, $settingsManager) {
        $request = new Request();
        $request->isDispatched(true);
        $event = [null, ['exception' => $exception, 'request' => $request]];
        $module = new SystemModule('foo/bar', $this->getTestDirPath());
        $serviceManager = new ServiceManager();
        $site = $this->createMock(Site::class);
        $site->method('config')
            ->willReturn([
                'throwDispatchErrors'=> false,
            ]);
        $serviceManager->set('site', $site);
        $serviceManager->set('settingsManager', $settingsManager);
        $module->setServiceManager($serviceManager);
        return [$module, $event, $request];
    }

    private function throwAccessDenied() {
        try {
            throw new AccessDeniedException();
        } catch (AccessDeniedException $e) {
        }
        return $e;
    }

    private function newSettingsManager($valueToReturn): SettingsManager {
        return new class($valueToReturn) extends SettingsManager {
            private $value;

            public function __construct($value) {
                $this->value = $value;
            }

            public function get(string $name, $moduleName) {
                if ($name === SystemModule::ACCESS_DENIED_ERROR_HANDLER && $moduleName === SystemModule::NAME) {
                    return $this->value;
                }
                throw new \UnexpectedValueException();
            }
        };
    }

    private function assertRequestHasHandlerAndException(Request $request, array $handler, \Exception $exception) {
        $this->assertFalse($request->isDispatched());
        $this->assertEquals($handler, $request->handler());
        $this->assertEquals($exception, $request->internalParam('error'));
        $this->assertEquals(Response::STATUS_CODE_403, $request->response()->getStatusCode());
    }
}