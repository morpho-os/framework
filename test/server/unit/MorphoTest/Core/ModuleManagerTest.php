<?php
namespace MorphoTest\Core;

use Morpho\Test\DbTestCase;
use Morpho\Core\ModuleManager;
use Morpho\Core\Module;
use Morpho\Db\Db;
use Morpho\Di\ServiceManager;
use Morpho\Web\Controller;

class ModuleManagerTest extends DbTestCase {
    public function setUp() {
        $db = $this->createDb();
        $db->dropAllTables(['module', 'module_event']);
        $db->createTableForClass('\Morpho\Core\Module');
        $db->createTableForClass('\Morpho\Core\Event');
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
        $module = $moduleManager->add(new ErrorHandlingTestModule(['name' => $moduleName]));
        $request = $this->mock('\Morpho\Web\Request');
        $request->expects($this->any())
            ->method('getModuleName')
            ->will($this->returnValue($moduleName));
        $request->expects($this->any())
            ->method('getControllerName')
            ->will($this->returnValue('error-handling-test-controller'));

        $moduleManager->on('dispatchError', [$module, 'errorListener']);

        $this->assertFalse($module->errorListenerCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module->errorListenerCalled());
    }

    public function testModuleOperations() {
        $moduleAutoloader = new \ArrayIterator([
            __CLASS__ . '\\My\\Module' => __FILE__,
            __CLASS__ . '\\NotInstalled\\Module' => $this->getTestDirPath() . '/NotInstalled/Module.php',
        ]);
        $moduleManager = $this->createModuleManager($this->createDb(), $moduleAutoloader);

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

        $moduleManager->add($module);

        $this->assertFalse($module->isInstallCalled());

        $moduleManager->install($moduleName);

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

        $moduleManager->enable($moduleName);

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

        $moduleManager->disable($moduleName);

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

        $moduleManager->uninstall($moduleName);

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

    public function testListDisabledModulesModuleHasId() {
        $db = $this->createDb();
        $moduleManager = $this->createModuleManager($db);
        $moduleName = 'foo';
        $module = new \MorphoTest\Core\ModuleManagerTest\My\Module(['name' => $moduleName]);
        $moduleManager->add($module);
        $moduleManager->install($moduleName);
        $moduleManager->enable($moduleName);
        $moduleManager->disable($moduleName);
        $moduleId = $module->getId();
        $this->assertNotEmpty($moduleId);
        $moduleManager = $this->createModuleManager($db);
        $this->assertEquals(['foo'], $moduleManager->listModules(ModuleManager::DISABLED));
        $this->assertEquals($moduleId, $moduleManager->get($moduleName)->getId());
        $this->assertFalse($moduleManager->get($moduleName)->isEnabled());
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
        $moduleManager->add($module);
        $request = $this->mock('\Morpho\Web\Request');
        $request->expects($this->any())
            ->method('getModuleName')
            ->will($this->returnValue($moduleName));
        $controllerName = 'my-controller';
        $request->expects($this->any())
            ->method('getControllerName')
            ->will($this->returnValue($controllerName));
        $this->assertFalse($module->get($controllerName)->isDispatchCalled());

        $moduleManager->dispatch($request);

        $this->assertTrue($module->get($controllerName)->isDispatchCalled());
    }

    private function createModuleManager(Db $db = null, $moduleAutoloader = null) {
        $moduleManager = new MyModuleManager($db ?: $this->mock('\Morpho\Db\Db'));
        $serviceManager = new ServiceManager();
        if (null !== $moduleAutoloader) {
            $serviceManager->set('moduleAutoloader', $moduleAutoloader);
        }
        $moduleManager->setServiceManager($serviceManager);
        return $moduleManager;
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

    protected function tryLoad(string $name) {
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
