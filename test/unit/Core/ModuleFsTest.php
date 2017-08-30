<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Core\ModuleFs as BaseModuleFs;
use Morpho\Test\TestCase;

class ModuleFsTest extends TestCase {
    private $vendorName;

    public function setUp() {
        parent::setUp();
        $this->vendorName = 'morpho-test';
    }

    public function testBaseModuleDirPathAccessors() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->newModuleFs($baseModuleDirPath);
        $this->assertEquals($baseModuleDirPath, $moduleFs->baseModuleDirPath());
    }

    public function testModuleNames() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->newModuleFs($baseModuleDirPath);
        $moduleNames = $moduleFs->moduleNames();
        $this->assertCount(3, $moduleNames);
        $this->assertContains("{$this->vendorName}/earth", $moduleNames);
        $this->assertContains("{$this->vendorName}/saturn", $moduleNames);
        $this->assertContains("{$this->vendorName}/mars", $moduleNames);
    }

    public function testModuleClass_ClassDoesNotExist() {
        $moduleFs = $this->newModuleFs($this->getTestDirPath());
        $this->assertFalse($moduleFs->moduleClass("{$this->vendorName}/saturn"));
    }

    public function testModuleClass_ClassExists() {
        $moduleFs = $this->newModuleFs($this->getTestDirPath());
        $this->assertEquals(self::class . '\\Mars\\Module', $moduleFs->moduleClass("{$this->vendorName}/mars"));
    }

    public function testModuleDirPath() {
        $baseModuleDirPath = $this->getTestDirPath();
        $moduleFs = $this->newModuleFs($baseModuleDirPath);
        $moduleName = "{$this->vendorName}/saturn";
        $this->assertEquals($baseModuleDirPath . "/saturn", $moduleFs->moduleDirPath($moduleName));
    }

    private function newModuleFs(string $baseModuleDirPath) {
        return new ModuleFs($baseModuleDirPath, $this->createTmpDir());
    }
}

class ModuleFs extends BaseModuleFs {
    private $cacheDirPath;

    public function __construct($baseModuleDirPath, $cacheDirPath) {
        parent::__construct($baseModuleDirPath);
        $this->cacheDirPath = $cacheDirPath;
    }

    public function cacheDirPath(): string {
        return $this->cacheDirPath;
    }
}