<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\TWithModuleDirs;

class TWithModuleDirsTest extends TestCase {
    private $usingModuleDirs;

    public function setUp() {
        $this->usingModuleDirs = new UsingTWithModuleDirs($this->getTestDirPath());
    }

    public function testViewDirPathAccessors() {
        $this->checkDirPathAccessor('viewDirPath');
    }

    public function testTestDirPathAccessors() {
        $this->checkDirPathAccessor('testDirPath');
    }

    private function checkDirPathAccessor(string $method): void {
        $oldDirPath = $this->usingModuleDirs->$method();
        $this->assertNotEmpty($oldDirPath);
        $newDirPath = '/a/b/c';
        $this->assertNull($this->usingModuleDirs->{'set' . $method}($newDirPath));
        $this->assertSame($newDirPath, $this->usingModuleDirs->$method());
    }
}

class UsingTWithModuleDirs {
    use TWithModuleDirs;

    private $dirPath;

    public function __construct($dirPath) {
        $this->dirPath = $dirPath;
    }

    public function dirPath(): string {
        return $this->dirPath;
    }
}