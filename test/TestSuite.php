<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest;

use Morpho\Test\Sut;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths(): iterable {
        $sut = Sut::instance();

        yield __DIR__ . '/unit/TestSuite.php';

        foreach (new \DirectoryIterator($sut->baseModuleDirPath()) as $path) {
            if ($path->isDot()) {
                continue;
            }
            $testDirPath = $path->getPathname() . '/' . Sut::TEST_DIR_NAME;
            if (is_dir($testDirPath)) {
                foreach ($this->testFilesInDir($testDirPath) as $file) {
                    yield $file->getPathname();
                }
            }
        }

        yield __DIR__ . '/functional/TestSuite.php';
    }
}
