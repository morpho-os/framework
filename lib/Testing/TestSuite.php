<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use PHPUnit\Framework\TestSuite as BaseTestSuite;

abstract class TestSuite extends BaseTestSuite {
    protected string $testFileRegexp = '~((Test|TestSuite)\.php|\.phpt)$~s';

    public static function suite() {
        $suite = new static();
        $suite->setName(get_class($suite));
        $filePaths = $suite->testFilePaths();
        $suite->addTestFiles($filePaths);
        return $suite;
    }

    public function testFilePaths(): iterable {
        $class = get_class($this);
        if ($class === self::class) {
            return [];
        }
        $curDirPath = dirname((new \ReflectionClass($class))->getFileName());
        return $this->testFilesInDir($curDirPath);
    }

    protected function testFilesInDir(string $dirPath) {
        return new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dirPath)
            ),
            $this->testFileRegexp
        );
    }
}
