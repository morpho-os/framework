<?php
namespace Morpho\Test;

use Morpho\Fs\Directory;
use Morpho\Base\NotImplementedException;
use PHPUnit\Framework\TestSuite as BaseTestSuite;

abstract class TestSuite extends BaseTestSuite {
    protected $testFileRegexp = '~(Test|TestSuite)\.php$~s';

    public static function suite() {
        $suite = new static();
        $suite->addTestFiles($suite->testFilePaths());
        return $suite;
    }

    /**
     * @return array An array of test files, that can contain descendants of \PHPUnit\Framework\TestSuite
     *               or \PHPUnit\Framework\TestCase. Classes can define the suite() static method like
     *               we do in this class and therefore suites can be nested.
     */
    public function testFilePaths() {
        return Directory::filePaths(
            $this->getTestDirPath(),
            $this->testFileRegexp
        );
    }

    /**
     * @return array|string
     */
    protected function getTestDirPath() {
        throw new NotImplementedException(__METHOD__);
    }
}
