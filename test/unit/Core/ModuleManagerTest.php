<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use const Morpho\Core\MODULE_DIR_PATH;
use Morpho\Base\ClassNotFoundException;
use Morpho\Base\Node;
use Morpho\Core\Controller;
use Morpho\Core\Module;
use Morpho\Core\ModuleManager;
use Morpho\Core\Request;
use Morpho\Db\Sql\Db;
use Morpho\Di\ServiceManager;
use Morpho\Test\DbTestCase;

class ModuleManagerTest extends DbTestCase {
    private $vendor = 'morpho-test';

    public function setUp() {
        parent::setUp();
        $db = $this->newDbConnection();
        $schemaManager = $db->schemaManager($db);
        $schemaManager->deleteAllTables(['module', 'module_event']);
        require MODULE_DIR_PATH . '/system/vendor/autoload.php';
        $schemaManager->createTables(\Morpho\System\Module::tableDefinitions());
    }

    public function testOffsetGet_ModuleWithoutModuleClass() {
        $moduleName = $this->vendor . '/saturn';
        $moduleFs = $this->newModuleFs([$moduleName]);

        $moduleFs->expects($this->once())
            ->method('moduleClass')
            ->with($this->equalTo($moduleName))
            ->will($this->returnValue(Module::class));
        $moduleManager = $this->newModuleManager(null, $moduleFs);

        $module = $moduleManager->offsetGet($moduleName);

        $this->assertEquals(Module::class, get_class($module));
        $this->assertEquals($moduleName, $module->name());
    }

    public function testOffsetGet_ThrowsExceptionForNonExistingModule() {
        $moduleName = 'some/non-existing';
        $moduleManager = $this->newModuleManager();
        $this->expectException(ClassNotFoundException::class, "Unable to load the module '$moduleName'");
        $moduleManager->offsetGet($moduleName);
    }

    public function testUninstalledModuleNames_CanUseComposerNamingStyle() {
        $modules = ['galaxy/earth', 'galaxy/saturn'];
        $moduleManager = $this->newModuleManager(null, $this->newModuleFs($modules));
        $this->assertEquals($modules, $moduleManager->uninstalledModuleNames());
    }

    public function testExceptionHandling() {
        $moduleManager = $this->newModuleManager();

        $moduleName = 'error-handling-test-module';
        $module = new ErrorHandlingTestModule($moduleName, $this->getTestDirPath());
        $moduleManager->append($module);

        $request = new class() extends Request {
            protected function newResponse() {
            }
        };

        $moduleManager->on('dispatchError', [$module, 'errorListener']);

        $this->assertFalse($module->errorListenerCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module->errorListenerCalled());
    }

    public function testModuleOperations() {
        $moduleFs = $this->newModuleFs([
            __CLASS__ . '\\My',
            __CLASS__ . '\\NotInstalled',
        ]);
        $moduleManager = $this->newModuleManager(null, $moduleFs);

        // 1. Check initial state of all available modules.
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $notInstalledModules = $allModules = [
            __CLASS__ . '\\My',
            __CLASS__ . '\\NotInstalled',
        ];
        $this->assertEquals(
            $notInstalledModules,
            $moduleManager->moduleNames(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->moduleNames(ModuleManager::ALL)
        );

        // 2. Install the module and check for changes.
        $moduleName = __CLASS__ . '\\My';

        $moduleClass = $moduleName . '\\Module';
        $module = new $moduleClass($moduleName, $this->getTestDirPath());

        $moduleManager->append($module);

        $this->assertFalse($module->isInstallCalled());

        $moduleManager->installModule($moduleName);

        $this->assertTrue($module->isInstallCalled());

        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED));

        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->moduleNames(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->moduleNames(ModuleManager::ALL)
        );

        // 3. Enable the module and check for changes.
        $this->assertFalse($module->isEnableCalled());

        $moduleManager->enableModule($moduleName);

        $this->assertTrue($module->isEnableCalled());

        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::ENABLED));
        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->moduleNames(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->moduleNames(ModuleManager::ALL)
        );

        // 4. Disable the module and check for changes.
        $this->assertFalse($module->isDisableCalled());

        $moduleManager->disableModule($moduleName);

        $this->assertTrue($module->isDisableCalled());

        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->moduleNames(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED));
        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->moduleNames(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->moduleNames(ModuleManager::ALL)
        );

        // 5. Uninstall the module and check for changes.
        $this->assertFalse($module->isUninstallCalled());

        $moduleManager->uninstallModule($moduleName);

        $this->assertTrue($module->isUninstallCalled());

        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->moduleNames(ModuleManager::ENABLED));
        $this->assertEquals(
            $notInstalledModules,
            $moduleManager->moduleNames(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->moduleNames(ModuleManager::ALL)
        );
    }

    public function testInterface() {
        $moduleManager = $this->newModuleManager();
        $this->assertInstanceOf('\Morpho\Base\Node', $moduleManager);
    }

    public function testDispatch_CallsDispatchMethodOfController() {
        $moduleManager = $this->newModuleManager();
        $moduleName = 'my-module';
        $module = new ModuleManagerTest\My\Module($moduleName, $this->getTestDirPath());
        $moduleManager->append($module);
        $controllerName = 'my-controller';
        $request = new class($moduleName, $controllerName) extends Request {
            public function newResponse() {
            }
        };
        $request->setHandler([$moduleName, $controllerName, 'some']);

        $this->assertFalse($module[$controllerName]->isDispatchCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module[$controllerName]->isDispatchCalled());
    }

    public function testDispatchLoopAccessors() {
        $this->checkAccessors($this->newModuleManager(), 30, 25, 'dispatchLoopLimit');
    }

    public function testDispatch_ThrowsExceptionAfterExceedingLimit() {
        $moduleManager = new class($this->createMock(Db::class), $this->createMock(\Morpho\Core\ModuleFs::class)) extends ModuleManager {
            public function controller($moduleName, $controllerName, $actionName): Controller {
                throw new \RuntimeException();
            }

            public function trigger(string $eventName, array $args = null) {
            }

            protected function actionNotFound($moduleName, $controllerName, $actionName): void {
                throw new \RuntimeException();
            }
        };
        $request = $this->createMock(Request::class);

        $limit = 30;
        $moduleManager->setDispatchLoopLimit($limit);
        /*
        $request->expects($this->any())
            ->method('isDispatched')
            ->will($this->returnValue(false));
        */
        $this->expectException(\RuntimeException::class, "Dispatch loop has occurred $limit times");

        $moduleManager->dispatch($request);
    }

    private function newModuleManager(Db $db = null, $moduleFs = null) {
        $moduleManager = new MyModuleManager(
            $db ?: $this->newDbConnection(),
            $moduleFs ?: $this->newModuleFs([])
        );
        $moduleManager->setServiceManager(new ServiceManager());
        return $moduleManager;
    }

    private function newModuleFs(array $modules) {
        $mock = $this->createMock(\Morpho\Core\ModuleFs::class);
        $mock->expects($this->any())
            ->method('moduleNames')
            ->will($this->returnValue(new \ArrayIterator($modules)));
        return $mock;
    }
}

class ErrorHandlingTestModule extends Module {
    private $errorListenerCalled = false;

    public function errorListener() {
        $this->errorListenerCalled = true;
    }

    public function errorListenerCalled() {
        return $this->errorListenerCalled;
    }

    public function offsetGet($name): Node {
        return $name === 'error-handling-test-controller' ? new ErrorHandlingTestController($name) : parent::offsetGet($name);
    }
}

class ErrorHandlingTestController extends Controller {
    public function dispatch($request): void {
        throw new ErrorHandlingTestModuleException('Some exception message');
    }
}

class ErrorHandlingTestModuleException extends \RuntimeException {
}

class MyModuleManager extends ModuleManager {
    protected function actionNotFound($moduleName, $controllerName, $actionName): void {
        throw new \RuntimeException();
    }
}

namespace MorphoTest\Unit\Core\ModuleManagerTest\My;

use Morpho\Base\Node;
use Morpho\Db\Sql\Db;

class Module extends \Morpho\Core\Module {
    protected $installCalled = false;
    protected $enableCalled = false;
    protected $disableCalled = false;
    protected $uninstallCalled = false;

    public function isInstallCalled() {
        return $this->installCalled;
    }

    public function install(Db $db) {
        $this->installCalled = true;
    }

    public function enable(Db $db) {
        $this->enableCalled = true;
    }

    public function isEnableCalled() {
        return $this->enableCalled;
    }

    public function uninstall(Db $db) {
        $this->uninstallCalled = true;
    }

    public function isUninstallCalled() {
        return $this->uninstallCalled;
    }

    public function disable(Db $db) {
        $this->disableCalled = true;
    }

    public function isDisableCalled() {
        return $this->disableCalled;
    }

    protected function loadChild(string $name): Node {
        if ($name === 'my-controller') {
            return new MyController('my-controller');
        }
        return parent::loadChild($name);
    }
}

class MyController extends \Morpho\Core\Controller {
    public $dispatchCalled = false;

    public function dispatch($request): void {
        $this->dispatchCalled = true;
    }

    public function isDispatchCalled() {
        return $this->dispatchCalled;
    }
}
