<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use const Morpho\Core\CONFIG_FILE_NAME;
use Morpho\Core\Fs;
use const Morpho\Core\MODULE_DIR_NAME;
use Morpho\Test\TestCase;

class FsTest extends TestCase {
    private $fs;
    private $vendorName = 'morpho-test';

    public function setUp() {
        $this->fs = $this->newFs();
    }

    public function testBaseModuleDirPathAccessors() {
        $this->assertSame($this->getTestDirPath() . '/' . MODULE_DIR_NAME, $this->fs->baseModuleDirPath());
        $newBaseModuleDirPath = $this->tmpDirPath();
        $this->assertNull($this->fs->setBaseModuleDirPath($newBaseModuleDirPath));
        $this->assertSame($newBaseModuleDirPath, $this->fs->baseModuleDirPath());
    }

    public function testDetectBaseDirPath() {
        $this->assertStringStartsWith(Fs::detectBaseDirPath(__DIR__), str_replace('\\', '/', __DIR__));
    }

    public function testBaseDirPathAccessors() {
        $this->assertSame($this->getTestDirPath(), $this->fs->baseDirPath());

        $newBaseDirPath = $this->tmpDirPath();
        $this->assertNull($this->fs->setBaseDirPath($newBaseDirPath));
        $this->assertSame($newBaseDirPath, $this->fs->baseDirPath());
    }

    public function testConfigFileAccessors() {
        $this->assertSame($this->getTestDirPath() . '/' . MODULE_DIR_NAME . '/' . CONFIG_FILE_NAME, $this->fs->configFilePath());
        $newConfigFilePath = $this->getTestDirPath() . '/foo.php';
        $this->assertNull($this->fs->setConfigFilePath($newConfigFilePath));
        $this->assertSame($newConfigFilePath, $this->fs->configFilePath());
        $this->assertSame(['abc' => 'efg'], $this->fs->loadConfigFile());
    }

    public function testModuleNames() {
        $fs = $this->newConfiguredFs();
        $moduleNames = $fs->moduleNames();
        $this->assertCount(3, $moduleNames);
        $this->assertContains("{$this->vendorName}/earth", $moduleNames);
        $this->assertContains("{$this->vendorName}/saturn", $moduleNames);
        $this->assertContains("{$this->vendorName}/mars", $moduleNames);
    }

    public function testModuleClass_ClassDoesNotExist() {
        $this->assertFalse($this->newConfiguredFs()->moduleClass("{$this->vendorName}/saturn"));
    }

    public function testModuleClass_ClassExists() {
        $this->assertEquals(
            self::class . '\\Mars\\Module',
            $this->newConfiguredFs()->moduleClass("{$this->vendorName}/mars")
        );
    }

    public function testModuleDirPath() {
        $this->assertEquals(
            $this->getTestDirPath() . "/saturn",
            $this->newConfiguredFs()->moduleDirPath("{$this->vendorName}/saturn")
        );
    }

    private function newConfiguredFs() {
        $fs = $this->newFs();
        $fs->setBaseModuleDirPath($this->getTestDirPath());
        return $fs;
    }

    private function newFs() {
        $baseDirPath = $this->getTestDirPath();
        $cacheDirPath = $this->createTmpDir();
        return new class($baseDirPath, $cacheDirPath) extends Fs {
            private $cacheDirPath;
            public function __construct(string $baseDirPath, string $cacheDirPath) {
                parent::__construct($baseDirPath);
                $this->cacheDirPath = $cacheDirPath;
            }
            public function cacheDirPath(): string {
                return $this->cacheDirPath;
            }
        };
    }
}