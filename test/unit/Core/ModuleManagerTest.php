<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Base\ClassNotFoundException;
use Morpho\Base\Node;
use Morpho\Core\Controller;
use Morpho\Core\ModuleFs;
use Morpho\Core\ModuleInstaller;
use Morpho\Core\ModuleManager;
use const Morpho\Core\RC_DIR_NAME;
use Morpho\Core\Request;
use const Morpho\Core\SCHEMA_FILE_NAME;
use Morpho\Db\Sql\Db;
use Morpho\Di\ServiceManager;
use Morpho\Test\DbTestCase;
use Morpho\Core\Module;

class ModuleManagerTest extends DbTestCase {
    private $vendor = 'morpho-test';

    public function setUp() {
        parent::setUp();
        $db = $this->newDbConnection();
        $schemaManager = $db->schemaManager($db);
        $schemaManager->deleteAllTables(['module', 'module_event']);
        $schemaManager->createTables(require $this->sut()->baseModuleDirPath() . '/system/' . RC_DIR_NAME . '/' . SCHEMA_FILE_NAME);
    }

    public function testOffsetGet_ModuleWithoutModuleClass() {
        $moduleName = $this->vendor . '/saturn';
        $fs = $this->newFs([$moduleName]);

        $fs->expects($this->once())
            ->method('moduleClass')
            ->with($this->equalTo($moduleName))
            ->will($this->returnValue(Module::class));
        $moduleManager = $this->newModuleManager(null, $fs);

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
        $moduleManager = $this->newModuleManager(null, $this->newFs($modules));
        $this->assertEquals($modules, $moduleManager->uninstalledModuleNames());
    }

    public function testExceptionHandling() {
        $moduleManager = $this->newModuleManager();

        $moduleName = 'error-handling-test-module';
        $fs = $this->createMock(ModuleFs::class);
        $module = new ErrorHandlingTestModule($moduleName, $fs);
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
        $fs = $this->newFs([
            __CLASS__ . '\\My',
            __CLASS__ . '\\NotInstalled',
        ]);
        $moduleInstaller = $this->createMock(ModuleInstaller::class);
        $moduleManager = new MyModuleManager($this->newDbConnection($this->dbConfig()), $fs);
        $this->configureModuleInstallerMockForModuleOperations($moduleInstaller, $moduleManager);
        $services = ['moduleInstaller' => $moduleInstaller];
        $moduleManager->setServiceManager(new ServiceManager($services));

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
        $fs = $this->createMock(ModuleFs::class);
        $module = new $moduleClass($moduleName, $fs);

        $moduleManager->append($module);

        $moduleManager->installModule($moduleName);

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
        $moduleManager->enableModule($moduleName);

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
        $moduleManager->disableModule($moduleName);

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
        $moduleManager->uninstallModule($moduleName);

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
        $fs = $this->createMock(ModuleFs::class);
        $module = new ModuleManagerTest\My\Module($moduleName, $fs);
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

    public function testMaxNoOfDispatchIterationsAccessors() {
        $this->checkAccessors($this->newModuleManager(), 30, 25, 'maxNoOfDispatchIterations');
    }

    public function testDispatch_ThrowsExceptionAfterExceedingLimit() {
        $moduleManager = new class($this->createMock(Db::class), $this->createMock(\Morpho\Core\Fs::class)) extends ModuleManager {
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
        $moduleManager->setMaxNoOfDispatchIterations($limit);
        $this->expectException(\RuntimeException::class, "Dispatch loop has occurred $limit times");

        $moduleManager->dispatch($request);
    }

    private function newModuleManager(Db $db = null, $fs = null, array $services = null) {
        $moduleManager = new MyModuleManager(
            $db ?: $this->newDbConnection(),
            $fs ?: $this->newFs([])
        );
        $moduleManager->setServiceManager(new ServiceManager($services));
        return $moduleManager;
    }

    private function newFs(array $modules) {
        $mock = $this->createMock(\Morpho\Core\Fs::class);
        $mock->expects($this->any())
            ->method('moduleNames')
            ->will($this->returnValue(new \ArrayIterator($modules)));
        return $mock;
    }

    private function configureModuleInstallerMockForModuleOperations($moduleInstaller, $moduleManager) {
        $appendExpectation = function (string $method) use ($moduleInstaller, $moduleManager) {
            $moduleInstaller->expects($this->once())
                ->method($method)
                ->with($this->isType('string'), $this->isInstanceOf($moduleManager))
                ->willReturn(null);
        };
        $appendExpectation('installModule');
        $appendExpectation('enableModule');
        $appendExpectation('disableModule');
        $appendExpectation('uninstallModule');
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
