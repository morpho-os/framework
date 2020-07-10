<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use const Morpho\App\TEST_DIR_NAME;
use Morpho\Testing\Sut;

class ModuleTestSuite extends \Morpho\Testing\TestSuite {
    public function testFilePaths(): iterable {
        $sut = Sut::instance();
        foreach ($sut->serverModuleDirIt() as $dirPath) {
            $testDirPath = $dirPath. '/' . TEST_DIR_NAME;
            if (\is_dir($testDirPath)) {
                foreach ($this->testFilesInDir($testDirPath) as $file) {
                    yield $file->getPathname();
                }
            }
        }
    }
}
