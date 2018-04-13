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
        })();
        $expected = [
            // SecondParentController.php
            [
                'module'     => 'self/box',
                'controller' => 'SecondParent',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\SecondParentController',
                'filePath'   => $testDirPath . '/inheritance/SecondParentController.php',
            ],
            // FirstParentController.php
            [
                'module'     => 'store/product',
                'controller' => 'FirstParent',
                'action'     => 'firstParent',
                'class'      => __CLASS__ . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
            ],
            [
                'module'     => 'store/product',
                'controller' => 'FirstParent',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
            ],
            // ChildController.php
            [
                'module'     => 'store/product',
                'controller' => 'Child',
                'action'     => 'child',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
            ],
            [
                'module'     => 'store/product',
                'controller' => 'Child',
                'action'     => 'firstParent',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
            ],
            [
                'module'     => 'store/product',
                'controller' => 'Child',
                'action'     => 'secondParent',
                'class'      => __CLASS__ . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
            ],
            // My2Controller.php
            [
                'module'     => 'foo/bar',
                'controller' => 'MyFirst2',
                'action'     => 'foo2',
                'class'      => __CLASS__ . '\\MyFirst2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
            ],
            [
                'module'     => 'foo/bar',
                'controller' => 'MySecond2',
                'action'     => 'doSomething2',
                'class'      => __CLASS__ . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
            ],
            [
                'module'     => 'foo/bar',
                'controller' => 'MySecond2',
                'action'     => 'process2',
                'class'      => __CLASS__ . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
            ],
            // My1Controller.php
            [
                'module'     => 'random/planet',
                'controller' => 'My1First',
                'action'     => 'foo1',
                'class'      => __CLASS__ . '\\My1FirstController',
                'filePath'   => $testDirPath . '/My1Controller.php',
            ],
            [
                'module'     => 'random/planet',
                'controller' => 'MySecond1',
                'action'     => 'doSomething1',
                'class'      => __CLASS__ . '\\MySecond1Controller',
                'filePath'   => $testDirPath . '/My1Controller.php',
            ],
            [
                'module'     => 'random/planet',
                'controller' => 'MySecond1',
                'action'     => 'process1',
                'class'      => __CLASS__ . '\\MySecond1Controller',
                'filePath'   => $testDirPath . '/My1Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
            ],
            // My3Controller.php
            [
                'module'     => 'sunny/day',
                'controller' => 'MyFirst3',
                'action'     => 'foo3',
                'class'      => __CLASS__ . '\\MyFirst3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
            ],
            [
                'module'     => 'sunny/day',
                'controller' => 'MySecond3',
                'action'     => 'doSomething3',
                'class'      => __CLASS__ . '\\MySecond3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
            ],
            [
                'module'     => 'sunny/day',
                'controller' => 'MySecond3',
                'action'     => 'process3',
                'class'      => __CLASS__ . '\\MySecond3Controller',
                'filePath'   => $testDirPath . '/My3Controller.php',
                'docComment' => '/**
     * @foo Bar
     */',
            ],
        ];
        $this->assertEquals(
            $expected,
            iterator_to_array($actionMetaProvider($controllerFileMetas), false)
        );
    }
}
