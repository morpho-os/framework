<?php declare(strict_types=1);
namespace MorphoTest;

use const Morpho\Core\MODULE_DIR_PATH;
use const Morpho\Core\TEST_DIR_NAME;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths(): iterable {
        /*
        foreach ($this->testFilesInDir(__DIR__ . '/unit') as $file) {
            yield $file->getPathname();
        }
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
        */
        yield __DIR__ . '/functional/TestSuite.php';
    }
}
