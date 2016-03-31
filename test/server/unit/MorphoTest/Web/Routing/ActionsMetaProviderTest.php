<?php
namespace MorphoTest\Web\Routing;

use Morpho\Core\ModulePathManager;
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
        $serviceManager->set('modulePathManager', new ModulePathManager($this->getTestDirPath()));
        $moduleManager = new class () {
            public function listEnabledModules() {
                return [
                    'Foo',
                    'Baz'
                ];
            }
        };
        $serviceManager->set('moduleManager', $moduleManager);
        $actionsMetaProvider->setServiceManager($serviceManager);
        $this->assertEquals(
            [
                [
                    'module' => 'Foo',
                    'controller' => 'MyFirst3',
                    'action' => 'foo3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MyFirst3Controller',
                ],
                [
                    'module' => 'Foo',
                    'controller' => 'MySecond3',
                    'action' => 'doSomething3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MySecond3Controller',
                ],
                [
                    'module' => 'Foo',
                    'controller' => 'MySecond3',
                    'action' => 'process3',
                    'filePath' => $this->getTestDirPath() . '/foo/controller/My3Controller.php',
                    'class' => self::class . '\\MySecond3Controller',
                    'docComment' => '/**
     * @foo Bar
     */',
                ],
                [
                    'module' => 'Baz',
                    'controller' => 'MyFirst2',
                    'action' => 'foo2',
                    'filePath' => $this->getTestDirPath() . '/baz/controller/My2Controller.php',
                    'class' => self::class . '\\MyFirst2Controller',
                ],
                [
                    'module' => 'Baz',
                    'controller' => 'MySecond2',
                    'action' => 'doSomething2',
                    'filePath' => $this->getTestDirPath() . '/baz/controller/My2Controller.php',
                    'class' => self::class . '\\MySecond2Controller',
                ],
                [
                    'module' => 'Baz',
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