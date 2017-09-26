<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);

namespace MorphoTest\Unit\Web;

use Morpho\Web\ModuleFs;
use Morpho\Web\SiteFs;

class SiteFsTest extends ModuleFsTest {
    public function dataForOtherDirPathAccessors() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath . '/' . SiteFs::PUBLIC_DIR_NAME,
                SiteFs::PUBLIC_DIR_NAME,
            ],
            [
                $testDirPath . '/' . SiteFs::UPLOAD_DIR_NAME,
                SiteFs::UPLOAD_DIR_NAME,
            ],
            [
                $testDirPath . '/' . SiteFs::CONFIG_DIR_NAME,
                SiteFs::CONFIG_DIR_NAME,
            ],
            [
                $testDirPath . '/' . SiteFs::CACHE_DIR_NAME,
                SiteFs::CACHE_DIR_NAME,
            ],
            [
                $testDirPath . '/' . SiteFs::LOG_DIR_NAME,
                SiteFs::LOG_DIR_NAME,
            ],
        ];
    }

    protected function newFs(...$args) {
        return new SiteFs(...$args);
    }

/*    public function testConfigFilePath() {
        $dirPath = $this->getTestDirPath();
        $fs = new SiteFs($dirPath);
        $this->assertEquals(
            $dirPath . '/' . SiteFs::CONFIG_DIR_NAME . '/' . SiteFs::CONFIG_FILE_NAME,
            $fs->configFilePath()
        );
    }*/

    public function testInheritance() {
        $this->assertInstanceOf(ModuleFs::class, $this->newFs($this->getTestDirPath()));
    }
}