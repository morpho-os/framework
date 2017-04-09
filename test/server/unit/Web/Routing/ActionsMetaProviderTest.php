<?php
namespace MorphoTest\Web\Routing;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Routing\ActionsMetaProvider;

class ActionsMetaProviderTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->vendorName = 'morpho-os-test';

        $testDirPath = $this->getTestDirPath();
        require_once $testDirPath . '/My1Controller.php';
        require_once $testDirPath . '/My2Controller.php';
        require_once $testDirPath . '/My3Controller.php';
        require_once $testDirPath . '/inheritance/SecondParentController.php';
        require_once $testDirPath . '/inheritance/FirstParentController.php';
        require_once $testDirPath . '/inheritance/ChildController.php';
    }
    
    public function testInterfaces() {
        $this->assertInstanceOf('\Traversable', new ActionsMetaProvider());
    }

    public function testIterator_Inheritance() {
        $baseModuleDirPath = $this->getTestDirPath();
        $projectName = 'inheritance';
        $controllerFilePaths = [
            "{$this->vendorName}/$projectName" => ["$baseModuleDirPath/$projectName/ChildController.php"],
        ];
        $enabledModules = [
            "{$this->vendorName}/$projectName",
        ];
        $actionsMetaProvider = $this->createActionsMetaProvider($controllerFilePaths, $enabledModules);
        $this->assertSetsEqual(
            [
                [
                    'module'     => "$this->vendorName/$projectName",
                    'controller' => 'Child',
                    'action'     => 'secondParent',
                    'class'      => self::class . '\\ChildController',
                ],
                [
                    'module'     => "$this->vendorName/$projectName",
                    'controller' => 'Child',
                    'action'     => 'firstParent',
                    'class'      => self::class . '\\ChildController',
                ],
                [
                    'module'     => "$this->vendorName/$projectName",
                    'controller' => 'Child',
                    'action'     => 'child',
                    'class'      => self::class . '\\ChildController',
                ],
            ],
            iterator_to_array($actionsMetaProvider, false)
        );
    }

    public function testIterator() {
        $baseDirPath = $this->getTestDirPath();
        $controllerFilePaths = [
            "{$this->vendorName}/bar" => [$baseDirPath . '/My1Controller.php'],
            "{$this->vendorName}/baz" => [$baseDirPath . '/My2Controller.php'],
            "{$this->vendorName}/foo" => [$baseDirPath . '/My3Controller.php'],
        ];
        $enabledModules = [
            "{$this->vendorName}/foo",
            "{$this->vendorName}/baz",
        ];
        $actionsMetaProvider = $this->createActionsMetaProvider($controllerFilePaths, $enabledModules);
        $expected = [
            [
                'module' => "$this->vendorName/foo",
                'controller' => 'MyFirst3',
                'action' => 'foo3',
                'class' => self::class . '\\MyFirst3Controller',
            ],
            [
                'module' => "$this->vendorName/foo",
                'controller' => 'MySecond3',
                'action' => 'doSomething3',
                'class' => self::class . '\\MySecond3Controller',
            ],
            [
                'module' => "$this->vendorName/foo",
                'controller' => 'MySecond3',
                'action' => 'process3',
                'class' => self::class . '\\MySecond3Controller',
                'docComment' => '/**
     * @foo Bar
     */',
            ],
            [
                'module' => "$this->vendorName/baz",
                'controller' => 'MyFirst2',
                'action' => 'foo2',
                'class' => self::class . '\\MyFirst2Controller',
            ],
            [
                'module' => "$this->vendorName/baz",
                'controller' => 'MySecond2',
                'action' => 'doSomething2',
                'class' => self::class . '\\MySecond2Controller',
            ],
            [
                'module' => "$this->vendorName/baz",
                'controller' => 'MySecond2',
                'action' => 'process2',
                'class' => self::class . '\\MySecond2Controller',
                'docComment' => '/**
     * @foo Bar
     */',
            ],
        ];
        $actual = iterator_to_array($actionsMetaProvider->getIterator(), false);
        $this->assertSetsEqual($expected, $actual);
    }

    private function createActionsMetaProvider(array $controllerFilePaths, array $enabledModules) {
        $moduleFs = new class ($controllerFilePaths) {
            private $controllerFilePaths;
            
            public function __construct(array $controllerFilePaths) {
                $this->controllerFilePaths = $controllerFilePaths;
            }

            public function moduleControllerFilePaths($moduleName) {
                return $this->controllerFilePaths[$moduleName] ?? [];
            }

            public function registerModuleAutoloader() {

            }
        };
        $moduleManager = new class ($moduleFs, $enabledModules) {
            private $moduleFs, $enabledModules;
            
            public function __construct($moduleFs, array $enabledModules) {
                $this->moduleFs = $moduleFs;
                $this->enabledModules = $enabledModules;
            }

            public function enabledModuleNames() {
                return $this->enabledModules;
            }

            public function moduleFs() {
                return $this->moduleFs;
            }
        };
        $serviceManager = new ServiceManager();
        $serviceManager->set('moduleManager', $moduleManager);
        $actionsMetaProvider = new ActionsMetaProvider();
        $actionsMetaProvider->setServiceManager($serviceManager);
        return $actionsMetaProvider;
    }
}