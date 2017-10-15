<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\Routing;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;
use Morpho\Web\Module;
use Morpho\Web\ModulePathManager;
use Morpho\Web\Routing\ControllerFileMetaProvider;

class ControllerFileMetaProviderTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new ControllerFileMetaProvider(new \ArrayObject()));
    }

    public function testInvoke() {
        $modules = [
            'foo' => [],
            'bar' => [],
            'baz' => [], // should not be included in result: controller directory does not exist.
        ];
        $testDirPath = $this->getTestDirPath();
        $moduleMocks = [];
        foreach ($modules as $name => $_) {
            $pathManager = $this->createConfiguredMock(
                ModulePathManager::class,
                [
                    'controllerDirPath' => $testDirPath . '/' . $name,
                ]
            );
            $module = $this->createConfiguredMock(
                Module::class,
                [
                    'pathManager' => $pathManager,
                ]
            );
            $moduleMocks[$name] = $module;
        }
        $moduleProvider = new class ($moduleMocks) extends \ArrayObject {
            private $moduleMocks;
            public function __construct($moduleMocks) {
                $this->moduleMocks = $moduleMocks;
            }
            public function offsetGet($name) {
                if (isset($this->moduleMocks[$name])) {
                    return $this->moduleMocks[$name];
                }
                throw new \UnexpectedValueException();
            }
        };
        $controllerFileMetaProvider = new ControllerFileMetaProvider($moduleProvider);
        $expected = [
            [
                'module' => 'bar',
                'filePath' => $testDirPath . '/bar/OrangeController.php',
            ],
            [
                'module' => 'bar',
                'filePath' => $testDirPath . '/bar/RedController.php',
            ],
            [
                'module' => 'foo',
                'filePath' => $testDirPath . '/foo/BlueController.php',
            ],
        ];
        $actual = iterator_to_array($controllerFileMetaProvider->__invoke($modules));
        usort($actual, function ($a, $b) {
            return strcmp($a['filePath'], $b['filePath']);
        });
        $this->assertEquals($expected, $actual);
    }
}
