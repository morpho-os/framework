<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Routing;

use Morpho\Base\IFn;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Routing\ActionMetaProvider;

class ActionMetaProviderTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new ActionMetaProvider());
    }

    public function testInvoke() {
        $testDirPath = $this->getTestDirPath();
        $actionMetaProvider = new ActionMetaProvider();
        $controllerFileMetas = (function () use ($testDirPath) {
            yield [
                'module' => 'self/box',
                'filePath' => $testDirPath . '/inheritance/SecondParentController.php',
            ];
            yield [
                'module' => 'store/product',
                'filePath' => $testDirPath . '/inheritance/FirstParentController.php',
            ];
            yield [
                'module' => 'store/product',
                'filePath' => $testDirPath . '/inheritance/ChildController.php',
            ];
            yield [
                'module' => 'foo/bar',
                'filePath' => $testDirPath . '/My2Controller.php',
            ];
            yield [
                'module' => 'random/planet',
                'filePath' => $testDirPath . '/My1Controller.php',
            ];
            yield [
                'module' => 'sunny/day',
                'filePath' => $testDirPath . '/My3Controller.php',
            ];
            yield [
                'module' => 'one/more',
                'filePath' => $testDirPath . '/BaseController.php',
            ];
            yield [
                'module' => 'one/more',
                'filePath' => $testDirPath . '/NotClassController.php',
            ];
            yield [
                'module' => 'one/more',
                'filePath' => $testDirPath . '/NotClass1Controller.php',
            ];
        })();
        $expected = [
            // SecondParentController.php
            [
                'module'     => 'self/box',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\SecondParentController',
                'filePath'   => $testDirPath . '/inheritance/SecondParentController.php',
                'method'     => 'secondParentAction',
            ],
            // FirstParentController.php
            [
                'module'     => 'store/product',
                'action'     => 'firstParent',
                'class'      => __CLASS__ . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
                'method'     => 'firstParentAction',
            ],
            [
                'module'     => 'store/product',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
                'method'     => 'secondParentAction',
            ],
            // ChildController.php
            [
                'module'     => 'store/product',
                'action'     => 'child',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'childAction',
            ],
            [
                'module'     => 'store/product',
                'action'     => 'firstParent',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'firstParentAction',
            ],
            [
                'module'     => 'store/product',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'secondParentAction',
            ],
            // My2Controller.php
            [
                'module'     => 'foo/bar',
                'action'     => 'foo2',
                'class'      => __CLASS__ . '\\MyFirst2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'method'     => 'foo2Action',
            ],
            [
                'module'     => 'foo/bar',
                'action'     => 'doSomething2',
                'class'      => __CLASS__ . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'method'     => 'doSomething2Action',
            ],
            [
                'module'     => 'foo/bar',
                'action'     => 'process2',
                'class'      => __CLASS__ . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
                'method'     => 'process2Action',
            ],
            // My1Controller.php
            [
                'module'     => 'random/planet',
                'action'     => 'foo1',
                'class'      => __CLASS__ . '\\My1FirstController',
                'filePath'   => $testDirPath . '/My1Controller.php',
                'method'     => 'foo1Action',
            ],
            [
                'module'     => 'random/planet',
                'action'     => 'doSomething1',
                'class'      => __CLASS__ . '\\MySecond1Controller',
                'filePath'   => $testDirPath . '/My1Controller.php',
                'method'     => 'doSomething1Action'
            ],
            [
                'module'     => 'random/planet',
                'action'     => 'process1',
                'class'      => __CLASS__ . '\\MySecond1Controller',
                'filePath'   => $testDirPath . '/My1Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
                'method'     => 'process1Action',
            ],
            // My3Controller.php
            [
                'module'     => 'sunny/day',
                'action'     => 'foo3',
                'class'      => __CLASS__ . '\\MyFirst3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
                'method'     => 'foo3Action',
            ],
            [
                'module'     => 'sunny/day',
                'action'     => 'doSomething3',
                'class'      => __CLASS__ . '\\MySecond3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
                'method'     => 'doSomething3Action',
            ],
            [
                'module'     => 'sunny/day',
                'action'     => 'process3',
                'class'      => __CLASS__ . '\\MySecond3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
                'method'     => 'process3Action',
            ],
        ];
        $this->assertEquals(
            $expected,
            \iterator_to_array($actionMetaProvider($controllerFileMetas), false)
        );
    }

    public function testAnnotations_NoRoutesAnnotation() {
        $controllerDirPath = $this->getTestDirPath();
        $controllerFileMetas = [
            [
                'module' => 'test/annotations',
                'filePath' => $controllerDirPath . '/NoRoutesController.php',
           ]
        ];
        $actionMetaProvider = new ActionMetaProvider();
        $this->assertSame([], \iterator_to_array($actionMetaProvider->__invoke($controllerFileMetas)));
    }
}
