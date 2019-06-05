<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Routing;

use Morpho\Base\IFn;
use Morpho\App\ModuleIndex;
use Morpho\Testing\TestCase;
use Morpho\App\Module;
use Morpho\App\Web\Routing\ControllerFileMetaProvider;

class ControllerFileMetaProviderTest extends TestCase {
    public function testInterface() {
        /** @noinspection PhpParamsInspection */
        $this->assertInstanceOf(IFn::class, new ControllerFileMetaProvider($this->createMock(ModuleIndex::class)));
    }

    public function testInvoke() {
        $modules = [
            'foo',
            'bar',
            'baz', // should not be included in result: controller directory does not exist.
        ];
        $testDirPath = $this->getTestDirPath();
        $moduleIndex = $this->createMock(ModuleIndex::class);
        foreach ($modules as $name => $_) {
            $moduleIndex->expects($this->any())
                ->method('module')
                ->will($this->returnCallback(function ($moduleName) use ($name, $testDirPath) {
                    return new Module($moduleName, [
                        'path' => [
                            'controllerDirPath' => $testDirPath . '/' . $moduleName,
                        ],
                    ]);
                }));
        }
        /** @noinspection PhpParamsInspection */
        $controllerFileMetaProvider = new ControllerFileMetaProvider($moduleIndex);
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
        /** @noinspection PhpParamsInspection */
        $actual = \iterator_to_array($controllerFileMetaProvider->__invoke($modules));
        \usort($actual, function ($a, $b) {
            return \strcmp($a['filePath'], $b['filePath']);
        });
        $this->assertEquals($expected, $actual);
    }
}
