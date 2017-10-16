<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);

namespace MorphoTest\Unit\Web;

use const Morpho\Web\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Web\LOG_DIR_NAME;
use Morpho\Web\ModulePathManager;
use const Morpho\Web\PUBLIC_DIR_NAME;
use Morpho\Web\SitePathManager;
use const Morpho\Web\UPLOAD_DIR_NAME;

class SitePathManagerTest extends ModulePathManagerTest {
    public function dataForOtherDirPathAccessors() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath . '/' . PUBLIC_DIR_NAME,
                PUBLIC_DIR_NAME,
            ],
            [
                $testDirPath . '/' . UPLOAD_DIR_NAME,
                UPLOAD_DIR_NAME,
            ],
            [
                $testDirPath . '/' . CONFIG_DIR_NAME,
                CONFIG_DIR_NAME,
            ],
            [
                $testDirPath . '/' . CACHE_DIR_NAME,
                CACHE_DIR_NAME,
            ],
            [
                $testDirPath . '/' . LOG_DIR_NAME,
                LOG_DIR_NAME,
            ],
        ];
    }

    protected function newPathManager(...$args) {
        return new SitePathManager(...$args);
    }

    public function testInheritance() {
        $this->assertInstanceOf(ModulePathManager::class, $this->newPathManager($this->getTestDirPath()));
    }
}