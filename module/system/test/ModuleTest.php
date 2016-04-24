<?php
namespace MorphoTest\SystemTest;

use Morpho\Core\SettingManager;
use Morpho\Di\ServiceManager;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\Request;
use Morpho\Test\DbTestCase;
use Morpho\Web\Response;
use Morpho\System\Module as SystemModule;

class ModuleTest extends DbTestCase {
    public function setUp() {
        parent::setUp();
        $db = $this->db();
        $schemaManager = $db->schemaManager();
        $schemaManager->deleteAllTables();
        $schemaManager->createTables(SystemModule::getTableDefinitions());
    }

    public function testDispatchError_SetsDefaultHandlerForAccessDenied() {
        $settingManager = $this->createSettingManager(false);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingManager);

        $module->dispatchError($event);

        $this->assertRequestHasHandlerAndException(
            $request,
            SystemModule::defaultErrorHandler(SystemModule::ACCESS_DENIED_ERROR),
            $exception
        );
    }

    public function testDispatchError_SetsUserDefinedHandlerIfSetForAccessDenied() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingManager = $this->createSettingManager($handler);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingManager);

        $module->dispatchError($event);

        $this->assertRequestHasHandlerAndException($request, $handler, $exception);
    }

    public function testDispatchError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        $handler = ['My', 'Foo', 'handleMe'];
        $settingManager = $this->createSettingManager($handler);
        $exception = $this->throwAccessDenied();
        list($module, $event, $request) = $this->initModuleForDispatchError($exception, $settingManager);

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

    private function initModuleForDispatchError(\Exception $exception, $settingManager) {
        $request = new Request();
        $request->isDispatched(true);
        $event = [null, ['exception' => $exception, 'request' => $request]];
        $module = new SystemModule();
        $serviceManager = new ServiceManager();
        $siteManager = $this->createMock('\\Morpho\\Web\\SiteManager');
        $siteManager->method('getCurrentSiteConfig')
            ->will($this->returnValue(['throwDispatchErrors' => false]));
        $serviceManager->set('siteManager', $siteManager);
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

    private function assertRequestHasHandlerAndException(Request $request, array $handler, \Exception $exception) {
        $this->assertFalse($request->isDispatched());
        $this->assertEquals($handler, $request->getHandler());
        $this->assertEquals($exception, $request->getInternalParam('error'));
        $this->assertEquals(Response::STATUS_CODE_403, $request->getResponse()->getStatusCode());
    }
}