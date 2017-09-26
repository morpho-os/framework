<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\ModuleFs;

class ModuleFsTest extends TestCase {
    public function dataForOtherDirPathAccessors() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath,
                '',
            ],
            [
                $testDirPath . '/' . ModuleFs::TEST_DIR_NAME,
                ModuleFs::TEST_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModuleFs::VIEW_DIR_NAME,
                ModuleFs::VIEW_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModuleFs::LIB_DIR_NAME . '/' . ModuleFs::CONTROLLER_DIR_NAME,
                ModuleFs::CONTROLLER_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModuleFs::LIB_DIR_NAME,
                ModuleFs::LIB_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModuleFs::RC_DIR_NAME,
                ModuleFs::RC_DIR_NAME,
            ],
            [
                $testDirPath . '/' . ModuleFs::TMP_DIR_NAME,
                ModuleFs::TMP_DIR_NAME,
            ],
        ];
    }

    /**
     * @dataProvider dataForOtherDirPathAccessors
     */
    public function testOtherDirPathAccessors($expectedDirPath, $dirName) {
        $fs = $this->newFs($this->getTestDirPath());
        $this->checkDirPathAccessors($fs, $dirName, $expectedDirPath);
    }

    protected function newFs(...$args) {
        return new ModuleFs(...$args);
    }

    private function checkDirPathAccessors($fs, $dirName, $expectedDirPath) {
        $setter = 'set' . $dirName . 'DirPath';
        $getter = $dirName . 'DirPath';
        $this->assertEquals(
            $expectedDirPath,
            $fs->$getter()
        );
        $newDirPath = '/some/random/dir';
        $this->assertNull($fs->$setter($newDirPath));
        $this->assertEquals($newDirPath, $fs->$getter());
    }
}