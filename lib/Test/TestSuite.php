<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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

    protected function sut() {
        return Sut::instance();
    }
}
