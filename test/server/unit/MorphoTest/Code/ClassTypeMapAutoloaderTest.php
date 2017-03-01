<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\ClassTypeMapAutoloader;

class ClassTypeMapAutoloaderTest extends TestCase {
    public function tearDown() {
        $mapFilePath = $this->mapFilePath();
        if (is_file($mapFilePath)) {
            unlink($mapFilePath);
        }
    }

    public function testAutoload() {
        $regexp = '{\.php$}si';
        $dirPath = $this->_testDirPath();
        $mapFilePath = $this->mapFilePath();
        $autoloader = new ClassTypeMapAutoloader($mapFilePath, $dirPath, $regexp);

        $this->assertFalse(file_exists($mapFilePath));

        $class = __CLASS__ . '\\Foo';
        $this->assertFalse($autoloader->autoload($class . 'Invalid'));
        $this->assertTrue($autoloader->autoload($class));
        $this->assertTrue(file_exists($mapFilePath));

        $autoloader->clearMap();

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testClearMap_ClearEmptyMapDoesNotThrowException() {
        $autoloader = new ClassTypeMapAutoloader($this->mapFilePath(), $this->_testDirPath());
        $autoloader->clearMap();
        $this->markTestAsNotRisky();
    }

    public function testCaching() {
        $mapFilePath = $this->mapFilePath();
        $dirPath = $this->_testDirPath();
        $class = __CLASS__ . '\\Foo1';

        $this->assertFalse(file_exists($mapFilePath));

        $autoloader = new ClassTypeMapAutoloader($mapFilePath, $dirPath);
        $autoloader->useCache(false);
        $this->assertTrue($autoloader->autoload($class));

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testUseCache() {
        $this->assertBoolAccessor([new ClassTypeMapAutoloader(null, null), 'useCache'], true);
    }

    protected function mapFilePath() {
        return $this->tmpDirPath() . '/' . md5(__METHOD__) . '.php';
    }
}
