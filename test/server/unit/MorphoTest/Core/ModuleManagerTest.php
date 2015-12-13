<?php
namespace MorphoTest\Core;

use Morpho\Core\Request;
use Morpho\Test\DbTestCase;
use Morpho\Core\ModuleManager;
use Morpho\Core\Module;
use Morpho\Db\Db;
use Morpho\Di\ServiceManager;
use Morpho\Web\Controller;

class ModuleManagerTest extends DbTestCase {
    public function setUp() {
        $db = $this->createDb();
        $db->deleteAllTables(['module', 'module_event']);
        $this->createDbTables($db);
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
        $moduleClassLoader = new \ArrayIterator([
            __CLASS__ . '\\My\\Module' => __FILE__,
            __CLASS__ . '\\NotInstalled\\Module' => $this->getTestDirPath() . '/NotInstalled/Module.php',
        ]);
        $moduleManager = $this->createModuleManager($this->createDb(), $moduleClassLoader);

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
        $this->assertInstanceOf('\Morpho\Di\IServiceManagerAware', $moduleManager);
    }

    public function testDispatchCallsDispatchMethodOfController() {
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

    private function createModuleManager(Db $db = null, $moduleClassLoader = null) {
        $moduleManager = new MyModuleManager($db ?: $this->createDb());
        $serviceManager = new ServiceManager();
        if (null !== $moduleClassLoader) {
            $serviceManager->set('moduleClassLoader', $moduleClassLoader);
        }
        $moduleManager->setServiceManager($serviceManager);
        return $moduleManager;
    }
    private function createDbTables(Db $db) {
        $db->createTables(\System\Module::getTableDefinitions());
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

    public function get(string $name): \Morpho\Base\Node {
        return $name === 'error-handling-test-controller' ? new ErrorHandlingTestController() : parent::get($name);
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
    protected $loadable = ['foo' => 'MorphoTest\Core\ModuleManagerTest\My\Module'];

    protected function actionNotFound($moduleName, $controllerName, $actionName) {
    }
}

namespace MorphoTest\Core\ModuleManagerTest\My;

use Morpho\Db\Db;

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

    protected function tryLoadChild(string $name) {
        if ($name === 'my-controller') {
            return new MyController(['name' => 'my-controller']);
        }
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
