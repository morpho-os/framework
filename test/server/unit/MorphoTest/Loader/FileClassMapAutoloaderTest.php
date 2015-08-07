<?php
namespace MorphoTest\Loader;

use Morpho\Test\TestCase;
use Morpho\Loader\FileClassMapAutoloader;

class FileClassMapAutoloaderTest extends TestCase {
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
        $autoloader = new FileClassMapAutoloader($mapFilePath, $dirPath, $regexp);

        $this->assertFalse(file_exists($mapFilePath));

        $class = __CLASS__ . '\\Foo';
        $this->assertFalse($autoloader->autoload($class . 'Invalid'));
        $this->assertEquals($class, $autoloader->autoload($class));
        $this->assertTrue(file_exists($mapFilePath));

        $autoloader->clearMap();

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testClearEmptyMapShouldNotThrowException() {
        $autoloader = new FileClassMapAutoloader($this->getMapFilePath(), $this->getTestDirPath());
        $autoloader->clearMap();
    }

    public function testCaching() {
        $mapFilePath = $this->getMapFilePath();
        $dirPath = $this->getTestDirPath();
        $class = __CLASS__ . '\\Foo1';

        $this->assertFalse(file_exists($mapFilePath));

        $autoloader = new FileClassMapAutoloader($mapFilePath, $dirPath);
        $autoloader->useCache(false);
        $this->assertEquals($class, $autoloader->autoload($class));

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testUseCache() {
        $this->assertBoolAccessor([new FileClassMapAutoloader(null, null), 'useCache'], true);
    }

    protected function getMapFilePath() {
        return $this->getTmpDirPath() . '/' . md5(__METHOD__) . '.php';
    }
}
