<?php
namespace MorphoTest\Core;

use Morpho\Base\Node;
// Triggers an error for some reason in Travis environment.
//use Morpho\Core\ModuleFs;
use Morpho\Core\Request;
use Morpho\Test\DbTestCase;
use Morpho\Core\ModuleManager;
use Morpho\Core\Module;
use Morpho\Db\Sql\Db;
use Morpho\Di\ServiceManager;
use Morpho\Web\Controller;

class ModuleManagerTest extends DbTestCase {
    public function setUp() {
        parent::setUp();
        $db = $this->db();
        $schemaManager = $db->schemaManager($db);
        $schemaManager->deleteAllTables(['module', 'module_event']);
        $schemaManager->createTables(\Morpho\System\Module::getTableDefinitions());
    }

    public function testGetChild_ModuleWithoutModuleClass() {
        $moduleName = 'morpho-test/saturn';
        $moduleNs = __CLASS__ . '\\Saturn';
        $moduleFs = $this->createModuleFs([$moduleName]);
        $moduleFs->expects($this->once())
            ->method('getModuleNamespace')
            ->with($this->equalTo($moduleName))
            ->will($this->returnValue($moduleNs));
        $moduleFs->expects($this->once())
            ->method('doesModuleExist')
            ->with($this->equalTo($moduleName))
            ->will($this->returnValue(true));
        $moduleManager = $this->createModuleManager(null, $moduleFs);
        $module = $moduleManager->getChild($moduleName);
        $this->assertEquals(Module::class, get_class($module));
        $this->assertEquals($moduleName, $module->getName());
        $this->assertEquals($moduleNs, $module->getModuleNamespace());
    }

    public function testGetChild_ThrowsExceptionForNonExistingModule() {
        $moduleName = 'some/non-existing';
        $moduleManager = $this->createModuleManager();
        $this->setExpectedException('\\Morpho\\Base\\ObjectNotFoundException', "Unable to load the module '$moduleName'");
        $moduleManager->getChild($moduleName);
    }

    public function testListUninstalledModules_CanUseComposerNamingStyle() {
        $moduleList = ['galaxy/earth', 'galaxy/saturn'];
        $moduleManager = $this->createModuleManager(null, $this->createModuleFs($moduleList));
        $this->assertEquals($moduleList, $moduleManager->listUninstalledModules());
    }

    public function testFallbackMode() {
        $this->assertBoolAccessor(
            [$this->createModuleManager(), 'isFallbackMode'],
            false
        );
    }

    public function testExceptionHandling() {
        $moduleManager = $this->createModuleManager();

        $moduleName = 'error-handling-test-module';
        $module = $moduleManager->addChild(new ErrorHandlingTestModule(['name' => $moduleName]));

        $request = new class($moduleName, 'error-handling-test-controller') extends Request {
            public function __construct($moduleName, $controllerName) {
                $this->moduleName = $moduleName;
                $this->controllerName = $controllerName;
            }

            public function getModuleName() {
                return $this->moduleName;
            }

            public function getControllerName() {
                return $this->controllerName;
            }

            public function createResponse() {

            }
        };

        $moduleManager->on('dispatchError', [$module, 'errorListener']);

        $this->assertFalse($module->errorListenerCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module->errorListenerCalled());
    }

    public function testModuleOperations() {
        $moduleFs = $this->createModuleFs([
            __CLASS__ . '\\My',
            __CLASS__ . '\\NotInstalled',
        ]);
        $moduleManager = $this->createModuleManager(null, $moduleFs);

        // 1. Check initial state of all available modules.
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $notInstalledModules = $allModules = [
            __CLASS__ . '\\My',
            __CLASS__ . '\\NotInstalled',
        ];
        $this->assertEquals(
            $notInstalledModules,
            $moduleManager->listModules(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->listModules(ModuleManager::ALL)
        );

        // 2. Install the module and check for changes.
        $moduleName = __CLASS__ . '\\My';

        $moduleClass = $moduleName . '\\Module';
        $module = new $moduleClass(['name' => $moduleName]);

        $moduleManager->addChild($module);

        $this->assertFalse($module->isInstallCalled());

        $moduleManager->installModule($moduleName);

        $this->assertTrue($module->isInstallCalled());

        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED));

        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->listModules(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->listModules(ModuleManager::ALL)
        );

        // 3. Enable the module and check for changes.
        $this->assertFalse($module->isEnableCalled());

        $moduleManager->enableModule($moduleName);

        $this->assertTrue($module->isEnableCalled());

        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::ENABLED));
        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->listModules(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->listModules(ModuleManager::ALL)
        );

        // 4. Disable the module and check for changes.
        $this->assertFalse($module->isDisableCalled());

        $moduleManager->disableModule($moduleName);

        $this->assertTrue($module->isDisableCalled());

        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([$moduleName], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED));
        $this->assertEquals(
            [
                __CLASS__ . '\\NotInstalled',
            ],
            $moduleManager->listModules(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->listModules(ModuleManager::ALL)
        );

        // 5. Uninstall the module and check for changes.
        $this->assertFalse($module->isUninstallCalled());

        $moduleManager->uninstallModule($moduleName);

        $this->assertTrue($module->isUninstallCalled());

        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED | ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals([], $moduleManager->listModules(ModuleManager::ENABLED));
        $this->assertEquals(
            $notInstalledModules,
            $moduleManager->listModules(ModuleManager::UNINSTALLED)
        );
        $this->assertEquals(
            $allModules,
            $moduleManager->listModules(ModuleManager::ALL)
        );
    }

    public function testInterfaces() {
        $moduleManager = $this->createModuleManager();
        $this->assertInstanceOf('\Morpho\Base\Node', $moduleManager);
    }

    public function testDispatch_CallsDispatchMethodOfController() {
        $moduleManager = $this->createModuleManager();
        $moduleName = 'my-module';
        $module = new \MorphoTest\Core\ModuleManagerTest\My\Module(['name' => $moduleName]);
        $moduleManager->addChild($module);

        $controllerName = 'my-controller';

        $request = new class($moduleName, $controllerName) extends Request {
            public function __construct($moduleName, $controllerName) {
                $this->moduleName = $moduleName;
                $this->controllerName = $controllerName;
            }

            public function getModuleName() {
                return $this->moduleName;
            }

            public function getControllerName() {
                return $this->controllerName;
            }

            public function createResponse() {

            }
        };

        $this->assertFalse($module->getChild($controllerName)->isDispatchCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module->getChild($controllerName)->isDispatchCalled());
    }

    private function createModuleManager(Db $db = null, $moduleFs = null) {
        $moduleManager = new MyModuleManager(
            $db ?: $this->db(),
            $moduleFs ?: $this->createModuleFs([])
        );
        $moduleManager->setServiceManager(new ServiceManager());
        return $moduleManager;
    }

    private function createModuleFs(array $modules) {
        $mock = $this->createMock(\Morpho\Core\ModuleFs::class);
        $mock->expects($this->any())
            ->method('getModuleNames')
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

    public function getChild(string $name): Node {
        return $name === 'error-handling-test-controller' ? new ErrorHandlingTestController() : parent::getChild($name);
    }
}

class ErrorHandlingTestController extends Controller {
    public function dispatch($request) {
        throw new ErrorHandlingTestModuleException('Some exception message');
    }
}

class ErrorHandlingTestModuleException extends \RuntimeException {
}

class MyModuleManager extends ModuleManager {
    protected function actionNotFound($moduleName, $controllerName, $actionName) {
    }
}

namespace MorphoTest\Core\ModuleManagerTest\My;

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
            return new MyController(['name' => 'my-controller']);
        }
        return parent::loadChild($name);
    }
}

class MyController extends \Morpho\Base\Node {
    public $dispatchCalled = false;

    public function dispatch() {
        $this->dispatchCalled = true;
    }

    public function isDispatchCalled() {
        return $this->dispatchCalled;
    }
}
