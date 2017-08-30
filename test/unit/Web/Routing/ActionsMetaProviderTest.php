<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\Routing;

use const Morpho\Core\VENDOR;
use Morpho\Test\TestCase;
use Morpho\Web\Routing\ActionsMetaProvider;

class ActionsMetaProviderTest extends TestCase {
    private $vendorName;

    public function setUp() {
        parent::setUp();
        $this->vendorName = VENDOR . '-test';

        $testDirPath = $this->getTestDirPath();
        require_once $testDirPath . '/My1Controller.php';
        require_once $testDirPath . '/My2Controller.php';
        require_once $testDirPath . '/My3Controller.php';
        require_once $testDirPath . '/inheritance/SecondParentController.php';
        require_once $testDirPath . '/inheritance/FirstParentController.php';
        require_once $testDirPath . '/inheritance/ChildController.php';
    }
    
    public function testInterface() {
        $this->assertInstanceOf('\Traversable', new ActionsMetaProvider(new \stdClass()));
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
        $actionsMetaProvider = $this->newActionsMetaProvider($controllerFilePaths, $enabledModules);
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
        $actionsMetaProvider = $this->newActionsMetaProvider($controllerFilePaths, $enabledModules);
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

    private function newActionsMetaProvider(array $controllerFilePaths, array $enabledModules) {
        $moduleManager = new class ($enabledModules) {
            private $enabledModules;

            public function __construct(array $enabledModules) {
                $this->enabledModules = $enabledModules;
            }

            public function enabledModuleNames() {
                return $this->enabledModules;
            }
        };
        $actionsMetaProvider = new ActionsMetaProvider($moduleManager);
        $actionsMetaProvider->setControllerFilePathsProvider(new class ($controllerFilePaths) {
            private $paths;
            public function __construct(array $paths) {
                $this->paths = $paths;
            }
            public function __invoke(string $moduleName): iterable {
                return $this->paths[$moduleName];
            }
        });
        return $actionsMetaProvider;
    }
}
