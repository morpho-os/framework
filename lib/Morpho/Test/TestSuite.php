<?php
namespace Morpho\Test;

use Morpho\Fs\Directory;
use Morpho\Base\NotImplementedException;

abstract class TestSuite extends \PHPUnit_Framework_TestSuite {
    protected $testFileRegexp = '{(Test|TestSuite)\.php$}s';

    public static function suite() {
        $suite = new static();
        $suite->addTestFiles($suite->listTestFiles());
        return $suite;
    }

    /**
     * @return array An array of test files, that can contain descendants of \PHPUnit_Framework_TestSuite
     *               or \PHPUnit_Framework_TestCase. Classes can define the suite() static method like
     *               we do in this class and therefore suites can be nested.
     */
    public function listTestFiles() {
        return Directory::listFiles(
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
