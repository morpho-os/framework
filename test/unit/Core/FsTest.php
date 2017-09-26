<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use const Morpho\Core\CONFIG_DIR_NAME;
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
        $this->checkDirAccessors(
            $this->getTestDirPath() . '/' . MODULE_DIR_NAME,
            'baseModuleDirPath'
        );
    }

    public function testDetectBaseDirPath() {
        $this->assertStringStartsWith(Fs::detectBaseDirPath(__DIR__), str_replace('\\', '/', __DIR__));
    }

    public function testBaseDirPathAccessors() {
        $this->checkDirAccessors($this->getTestDirPath(), 'baseDirPath');
    }

    public function testConfigDirAccessors() {
        $this->checkDirAccessors($this->getTestDirPath() . '/' . CONFIG_DIR_NAME, 'configDirPath');
    }

    public function testConfigFileAccessors() {
        $this->assertSame($this->getTestDirPath() . '/' . CONFIG_DIR_NAME . '/' . CONFIG_FILE_NAME, $this->fs->configFilePath());
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

    private function checkDirAccessors($initialValue, string $getter) {
        $this->assertSame($initialValue, $this->fs->$getter());
        $setter = 'set' . $getter;
        $newDirPath = $this->tmpDirPath();
        $this->assertNull($this->fs->$setter($newDirPath));
        $this->assertSame($newDirPath, $this->fs->$getter());
    }
}