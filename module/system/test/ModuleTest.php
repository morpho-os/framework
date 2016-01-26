<?php
namespace SystemTest;

use Morpho\Core\SettingManager;
use Morpho\Di\ServiceManager;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\Request;
use Morpho\Test\DbTestCase;
use System\Module as SystemModule;

class ModuleTest extends DbTestCase {
    public function setUp() {
        parent::setUp();
        $db = $this->createDb();
        $schemaManager = $db->schemaManager();
        $schemaManager->deleteAllTables();
        $schemaManager->createTables(SystemModule::getTableDefinitions());
    }

    public function testDispatchError_SetsDefaultHandlerForAccessDenied() {
        $settingManager = $this->createSettingManager(false);
        list($module, $event, $request) = $this->initModuleForDispatchError($settingManager);
        $module->dispatchError($event);
        $this->assertEquals(SystemModule::defaultErrorHandler(SystemModule::ACCESS_DENIED_ERROR), $request->getHandler());
        $this->assertFalse($request->isDispatched());
    }

    public function testDispatchError_SetsUserDefinedHandlerIfSetForAccessDenied() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingManager = $this->createSettingManager($handler);
        list($module, $event, $request) = $this->initModuleForDispatchError($settingManager);
        $module->dispatchError($event);
        $this->assertEquals($handler, $request->getHandler());
        $this->assertFalse($request->isDispatched());
    }

    public function testDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingManager = $this->createSettingManager($handler);
        list($module, $event, $request) = $this->initModuleForDispatchError($settingManager);
        $module->dispatchError($event);
        $this->assertEquals($handler, $request->getHandler());
        $this->assertFalse($request->isDispatched());
        try {
            $module->dispatchError($event);
            $this->fail();
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $event[1]['exception']);
        }
    }

    private function initModuleForDispatchError($settingManager) {
        $e = $this->throwAccessDenied();
        $request = new Request();
        $request->isDispatched(true);
        $event = [null, ['exception' => $e, 'request' => $request]];
        $module = new SystemModule();
        $serviceManager = new ServiceManager();
        $serviceManager->set('settingManager', $settingManager);
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

    private function createSettingManager($valueToReturn): SettingManager {
        return new class($valueToReturn) extends SettingManager {
            private $value;

            public function __construct($value) {
                $this->value = $value;
            }

            public function get($name, $moduleName) {
                if ($name === SystemModule::ACCESS_DENIED_ERROR_HANDLER && $moduleName === SystemModule::NAME) {
                    return $this->value;
                }
                throw new \UnexpectedValueException();
            }
        };
    }
}