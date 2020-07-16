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
            /*
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
            */
        })();
        $testNs = __CLASS__;
        $expected = [
            // SecondParentController
            [
                'module'     => 'self/box',
                'class'      => $testNs . '\\SecondParentController',
                'filePath'   => $testDirPath . '/inheritance/SecondParentController.php',
                'method'     => 'secondParent',
            ],
            // FirstParentController extends SecondParentController
            [
                'module'     => 'store/product',
                'class'      => $testNs . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
                'method'     => 'firstParent',
            ],
            [
                'module'     => 'store/product',
                'class'      => $testNs . '\\FirstParentController',
                'filePath'   => $testDirPath . '/inheritance/FirstParentController.php',
                'method'     => 'secondParent',
            ],
            // ChildController extends FirstParentController
            [
                'module'     => 'store/product',
                'class'      => $testNs . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'child',
            ],
            [
                'module'     => 'store/product',
                'class'      => $testNs . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'firstParent',
            ],
            [
                'module'     => 'store/product',
                'class'      => $testNs . '\\ChildController',
                'filePath'   => $testDirPath . '/inheritance/ChildController.php',
                'method'     => 'secondParent',
            ],
            // MyFirst2Controller extends Controller
            [
                'module'     => 'foo/bar',
                'class'      => $testNs . '\\MyFirst2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'method'     => 'foo2',
            ],
            // MySecond2Controller extends Controller
            [
                'module'     => 'foo/bar',
                'class'      => $testNs . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'method'     => 'doSomething2',
            ],
            // Third2Controller extends Controller
            [
                'module'     => 'foo/bar',
                'class'      => $testNs . '\\MySecond2Controller',
                'filePath'   => $testDirPath . '/My2Controller.php',
                'docComment' => '/**
     * @@foo Bar
     */',
                'method'     => 'process2',
            ],
        ];
        $actual = \iterator_to_array($actionMetaProvider($controllerFileMetas), false);
        $this->assertEquals($expected, $actual);
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
