<?php
namespace MorphoTest\Web\Routing;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Routing\ActionsMetaProvider;

class ActionsMetaProviderTest extends TestCase {
    public function testInterfaces() {
        $this->assertInstanceOf('\Traversable', new ActionsMetaProvider());
    }

    public function testIterator() {
        $actionsMetaProvider = new ActionsMetaProvider();
        $serviceManager = new ServiceManager();
        $vendorName = 'morpho-os-test';
        $moduleFs = new class ($this->getTestDirPath(), $vendorName) {
            private $baseModuleDirPath, $vendorName;
            public function __construct($baseModuleDirPath, $vendorName) {
                $this->baseModuleDirPath = $baseModuleDirPath;
                $this->vendorName = $vendorName;
            }

            public function getModuleControllerFilePaths($moduleName) {
                $map = [
                    "{$this->vendorName}/bar" => [$this->baseModuleDirPath . '/bar/' . CONTROLLER_DIR_NAME . '/My1Controller.php'],
                    "{$this->vendorName}/baz" => [$this->baseModuleDirPath . '/baz/' . CONTROLLER_DIR_NAME . '/My2Controller.php'],
                    "{$this->vendorName}/foo" => [$this->baseModuleDirPath . '/foo/' . CONTROLLER_DIR_NAME . '/My3Controller.php'],
                ];
                return $map[$moduleName] ?? [];
            }
        };
        $moduleManager = new class ($moduleFs, $vendorName) {
            private $moduleFs, $vendorName;
            public function __construct($moduleFs, $vendorName) {
                $this->moduleFs = $moduleFs;
                $this->vendorName = $vendorName;
            }

            public function listEnabledModules() {
                return [
                    "{$this->vendorName}/foo",
                    "{$this->vendorName}/baz",
                ];
            }

            public function getModuleFs() {
                return $this->moduleFs;
            }
        };
        $serviceManager->set('moduleManager', $moduleManager);
        $actionsMetaProvider->setServiceManager($serviceManager);
        $this->assertEquals(
            [
                [
                    'module' => "$vendorName/foo",
                    'controller' => 'MyFirst3',
                    'action' => 'foo3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MyFirst3Controller',
                ],
                [
                    'module' => "$vendorName/foo",
                    'controller' => 'MySecond3',
                    'action' => 'doSomething3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MySecond3Controller',
                ],
                [
                    'module' => "$vendorName/foo",
                    'controller' => 'MySecond3',
                    'action' => 'process3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MySecond3Controller',
                    'docComment' => '/**
     * @foo Bar
     */',
                ],
                [
                    'module' => "$vendorName/baz",
                    'controller' => 'MyFirst2',
                    'action' => 'foo2',
                    'filePath' => $this->getTestDirPath() . '/baz/controller/My2Controller.php',
                    'class' => self::class . '\\MyFirst2Controller',
                ],
                [
                    'module' => "$vendorName/baz",
                    'controller' => 'MySecond2',
                    'action' => 'doSomething2',
                    'filePath' => $this->getTestDirPath() . '/baz/controller/My2Controller.php',
                    'class' => self::class . '\\MySecond2Controller',
                ],
                [
                    'module' => "$vendorName/baz",
                    'controller' => 'MySecond2',
                    'action' => 'process2',
                    'filePath' => $this->getTestDirPath() . '/baz/controller/My2Controller.php',
                    'class' => self::class . '\\MySecond2Controller',
                    'docComment' => '/**
     * @foo Bar
     */',
                ],
            ],
            iterator_to_array($actionsMetaProvider->getIterator(), false)
        );
    }
}