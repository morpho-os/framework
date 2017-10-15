<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace MorphoTest\Unit\Web;

use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\RC_DIR_NAME;
use const Morpho\Core\TEST_DIR_NAME;
use const Morpho\Core\TMP_DIR_NAME;
use Morpho\Test\TestCase;
use Morpho\Web\ModulePathManager;

class ModulePathManagerTest extends TestCase {
    public function dataForOtherDirPathAccessors() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath . '/' . TEST_DIR_NAME,
                TEST_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModulePathManager::VIEW_DIR_NAME,
                ModulePathManager::VIEW_DIR_NAME,
            ],
            [
                $testDirPath . '/' . LIB_DIR_NAME,
                LIB_DIR_NAME,
            ],
            [
                $testDirPath . '/' . RC_DIR_NAME,
                RC_DIR_NAME,
            ],
            [
                $testDirPath . '/' . TMP_DIR_NAME,
                TMP_DIR_NAME,
            ],
        ];
    }

    /**
     * @dataProvider dataForOtherDirPathAccessors
     */
    public function testOtherDirPathAccessors($expectedDirPath, $dirName) {
        $pathManager = $this->newPathManager($this->getTestDirPath());
        $this->checkDirPathAccessors($pathManager, $dirName, $expectedDirPath);
    }

    public function testDirPath() {
        $testDirPath = $this->getTestDirPath() . '/123';
        $modulePathManager = new ModulePathManager($testDirPath);
        $this->assertSame($testDirPath, $modulePathManager->dirPath());
    }

    protected function newPathManager(...$args) {
        return new ModulePathManager(...$args);
    }

    private function checkDirPathAccessors($pathManager, $dirName, $expectedDirPath) {
        $setter = 'set' . $dirName . 'DirPath';
        $getter = $dirName . 'DirPath';
        $this->assertEquals(
            $expectedDirPath,
            $pathManager->$getter()
        );
        $newDirPath = '/some/random/dir';
        $this->assertNull($pathManager->$setter($newDirPath));
        $this->assertEquals($newDirPath, $pathManager->$getter());
    }
}