<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\ClassTypeMapAutoloader;

class ClassTypeMapAutoloaderTest extends TestCase {
    public function tearDown() {
        $mapFilePath = $this->getMapFilePath();
        if (is_file($mapFilePath)) {
            unlink($mapFilePath);
        }
    }

    public function testAutoload() {
        $regexp = '{\.php$}si';
        $dirPath = $this->getTestDirPath();
        $mapFilePath = $this->getMapFilePath();
        $autoloader = new ClassTypeMapAutoloader($mapFilePath, $dirPath, $regexp);

        $this->assertFalse(file_exists($mapFilePath));

        $class = __CLASS__ . '\\Foo';
        $this->assertFalse($autoloader->autoload($class . 'Invalid'));
        $this->assertTrue($autoloader->autoload($class));
        $this->assertTrue(file_exists($mapFilePath));

        $autoloader->clearMap();

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testClearEmptyMapShouldNotThrowException() {
        $autoloader = new ClassTypeMapAutoloader($this->getMapFilePath(), $this->getTestDirPath());
        $autoloader->clearMap();
    }

    public function testCaching() {
        $mapFilePath = $this->getMapFilePath();
        $dirPath = $this->getTestDirPath();
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

    protected function getMapFilePath() {
        return $this->tmpDirPath() . '/' . md5(__METHOD__) . '.php';
    }
}
