<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Core;

use Morpho\Core\ModuleIndex;
use Morpho\Core\IModuleIndexer;
use Morpho\Core\ModuleMeta;
use Morpho\Test\TestCase;

class ModuleIndexTest extends TestCase {
    public function testRebuild() {
        $moduleIndexer = $this->createMock(IModuleIndexer::class);
        $moduleIndex = $this->newModuleIndex($moduleIndexer);
        $moduleName = 'foo/bar';
        $moduleIndexer->expects($this->exactly(2))
            ->method('index')
            ->willReturnOnConsecutiveCalls([$moduleName => ['first']], [$moduleName => ['second']]);

        $this->assertSame('first', $moduleIndex->moduleMeta($moduleName)[0]);

        $moduleIndexer->expects($this->once())
            ->method('clear');

        $this->assertNull($moduleIndex->rebuild());

        $this->assertSame('second', $moduleIndex->moduleMeta($moduleName)[0]);
    }

    public function testModuleOperations() {
        $moduleIndex = $this->newModuleIndex($this->newModuleIndexer());

        $this->assertSame(['galaxy/neptune', 'galaxy/mars'], $moduleIndex->moduleNames());

        $this->assertTrue($moduleIndex->moduleExists('galaxy/neptune'));
        $this->assertFalse($moduleIndex->moduleExists('galaxy/invalid'));
    }

    public function testModuleMeta_ThrowsExceptionForNonExistentModule() {
        $moduleIndex = $this->newModuleIndex($this->newModuleIndexer());
        $this->expectException(\RuntimeException::class, "Unable to get meta for the module 'galaxy/invalid'");

        $moduleIndex->moduleMeta('galaxy/invalid');
    }
    
    public function testIter() {
        $moduleIndex = $this->newModuleIndex($this->newModuleIndexer());
        $this->assertInstanceOf(\Traversable::class, $moduleIndex);
        $i = 0;
        foreach ($moduleIndex as $moduleName) {
            $this->assertTrue(in_array($moduleName, ['galaxy/neptune', 'galaxy/mars'], true));
            $i++;
        }
        $this->assertSame(2, $i);
    }

    private function newModuleIndex($moduleIndexer) {
        return new class ($moduleIndexer) extends ModuleIndex {
            protected function newModuleMeta(string $moduleName, $meta): ModuleMeta {
                return new ModuleMeta($moduleName, $meta);
            }
        };
    }

    private function newModuleIndexer() {
        $moduleIndexer = $this->createConfiguredMock(IModuleIndexer::class, [
            'index' => [
                'galaxy/neptune' => [
                    'namespace' => __CLASS__ . '/Neptune',
                ],
                'galaxy/mars'    => [
                    'namespace' => __CLASS__ . '/Mars',
                ],
            ],
        ]);
        return $moduleIndexer;
    }
}