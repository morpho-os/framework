<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Code;

use Morpho\Code\Autoloading\ClassTypeMapAutoloader;
use Morpho\Test\TestCase;

class ClassTypeMapAutoloaderTest extends TestCase {
    public function tearDown() {
        $mapFilePath = $this->mapFilePath();
        if (is_file($mapFilePath)) {
            unlink($mapFilePath);
        }
    }

    public function testAutoload() {
        $regexp = '{\.php$}si';
        $dirPath = $this->getTestDirPath();
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
        $autoloader = new ClassTypeMapAutoloader($this->mapFilePath(), $this->getTestDirPath());
        $autoloader->clearMap();
        $this->markTestAsNotRisky();
    }

    public function testCaching() {
        $mapFilePath = $this->mapFilePath();
        $dirPath = $this->getTestDirPath();
        $class = __CLASS__ . '\\Foo1';

        $this->assertFalse(file_exists($mapFilePath));

        $autoloader = new ClassTypeMapAutoloader($mapFilePath, $dirPath);
        $autoloader->useCache(false);
        $this->assertTrue($autoloader->autoload($class));

        $this->assertFalse(file_exists($mapFilePath));
    }

    public function testUseCache() {
        $this->checkBoolAccessor([new ClassTypeMapAutoloader(null, null), 'useCache'], true);
    }

    protected function mapFilePath() {
        return $this->tmpDirPath() . '/' . md5(__METHOD__) . '.php';
    }
}
