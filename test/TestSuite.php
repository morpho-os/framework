<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest;

use const Morpho\Core\MODULE_DIR_PATH;
use const Morpho\Core\TEST_DIR_NAME;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths(): iterable {
        yield __DIR__ . '/unit/TestSuite.php';
        foreach (new \DirectoryIterator(MODULE_DIR_PATH) as $path) {
            if ($path->isDot()) {
                continue;
            }
            $testDirPath = $path->getPathname() . '/' . TEST_DIR_NAME;
            if (is_dir($testDirPath)) {
                foreach ($this->testFilesInDir($testDirPath) as $file) {
                    yield $file->getPathname();
                }
            }
        }
        yield __DIR__ . '/functional/TestSuite.php';
    }
}
