<?php
namespace Morpho\Test;

use PHPUnit\Framework\TestSuite as BaseTestSuite;

abstract class TestSuite extends BaseTestSuite {
    protected $testFileRegexp = '~[^/](Test|TestSuite)\.php$~s';

    public static function suite() {
        $suite = new static();
        $suite->addTestFiles($suite->testFilePaths());
        return $suite;
    }

    abstract public function testFilePaths(): iterable;

    protected function testFilesInDir(string $dirPath) {
        return new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath)
            ),
            $this->testFileRegexp
        );
    }
}
