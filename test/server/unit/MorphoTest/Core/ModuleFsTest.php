<?php
namespace MorphoTest\Core;

use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Test\TestCase;

class ModuleFsTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->vendorName = 'morpho-test';
    }

    public function tearDown() {
        parent::tearDown();
        $cacheFilePath = $this->tmpDirPath() . '/' . ModuleFs::CACHE_FILE_NAME;
        if (is_file($cacheFilePath)) {
            unlink($cacheFilePath);
        }
    }

    public function testBaseModuleDirPathAccessors() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $this->assertEquals($baseModuleDirPath, $moduleFs->getBaseModuleDirPath());
    }

    public function testGetModuleNames() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $moduleNames = $moduleFs->getModuleNames();
        $this->assertCount(3, $moduleNames);
        $this->assertContains("{$this->vendorName}/earth", $moduleNames);
        $this->assertContains("{$this->vendorName}/saturn", $moduleNames);
        $this->assertContains("{$this->vendorName}/mars", $moduleNames);
    }

    public function testGetModuleClass_ReturnsFalseWhenClassDoesNotExist() {
        $moduleFs = $this->createModuleFs($this->getTestDirPath());
        $this->assertNull($moduleFs->getModuleClass("{$this->vendorName}/saturn"));
    }

    public function testGetModuleClass_ReturnsClassWhenClassExists() {
        $moduleFs = $this->createModuleFs($this->getTestDirPath());
        $this->assertEquals('MorphoTest\\Core\\ModuleFsTest\\Mars\\Module', $moduleFs->getModuleClass("{$this->vendorName}/mars"));
    }
    
    public function testGetModuleDirPath() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $moduleName = "{$this->vendorName}/saturn";
        $this->assertEquals($baseModuleDirPath . "/saturn", $moduleFs->getModuleDirPath($moduleName));
    }

    public function testGetControllerFilePaths() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $this->assertEquals(
            [
                $baseModuleDirPath . '/saturn/' . CONTROLLER_DIR_NAME . '/FooController.php'
            ],
            $moduleFs->getModuleControllerFilePaths("{$this->vendorName}/saturn")
        );
    }

    private function createModuleFs(string $baseModuleDirPath) {
        return new ModuleFs($baseModuleDirPath, new \stdClass(), $this->tmpDirPath());
    }
}

class ModuleFs extends BaseModuleFs {
    private $baseCacheDirPath;

    public function __construct($baseModuleDirPath, $autoloader, $baseCacheDirPath) {
        parent::__construct($baseModuleDirPath, $autoloader);
        $this->baseCacheDirPath = $baseCacheDirPath;
    }

    public function getBaseCacheDirPath(): string {
        return $this->baseCacheDirPath;
    }

    public function registerModuleAutoloader(string $moduleName): bool {
        return false;
    }
}