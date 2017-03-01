<?php
namespace MorphoTest\Core;

use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Test\TestCase;

class ModuleFsTest extends TestCase {
    private $vendorName;

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
        $this->assertEquals($baseModuleDirPath, $moduleFs->baseModuleDirPath());
    }

    public function testModuleNames() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $moduleNames = $moduleFs->moduleNames();
        $this->assertCount(3, $moduleNames);
        $this->assertContains("{$this->vendorName}/earth", $moduleNames);
        $this->assertContains("{$this->vendorName}/saturn", $moduleNames);
        $this->assertContains("{$this->vendorName}/mars", $moduleNames);
    }

    public function testModuleClass_ReturnsFalseWhenClassDoesNotExist() {
        $moduleFs = $this->createModuleFs($this->getTestDirPath());
        $this->assertNull($moduleFs->moduleClass("{$this->vendorName}/saturn"));
    }

    public function testModuleClass_ReturnsClassWhenClassExists() {
        $moduleFs = $this->createModuleFs($this->getTestDirPath());
        $this->assertEquals('MorphoTest\\Core\\ModuleFsTest\\Mars\\Module', $moduleFs->moduleClass("{$this->vendorName}/mars"));
    }
    
    public function testModuleDirPath() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $moduleName = "{$this->vendorName}/saturn";
        $this->assertEquals($baseModuleDirPath . "/saturn", $moduleFs->moduleDirPath($moduleName));
    }

    public function testModuleControllerFilePaths() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->createModuleFs($baseModuleDirPath);
        $this->assertEquals(
            [
                $baseModuleDirPath . '/saturn/' . CONTROLLER_DIR_NAME . '/FooController.php'
            ],
            $moduleFs->moduleControllerFilePaths("{$this->vendorName}/saturn")
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

    public function baseCacheDirPath(): string {
        return $this->baseCacheDirPath;
    }

    public function registerModuleAutoloader(string $moduleName): bool {
        return false;
    }
}