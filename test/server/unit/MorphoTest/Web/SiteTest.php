<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Site;

class SiteTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->site = new Site(['dirPath' => $this->getTestDirPath(), 'name' => 'foo']);
    }

    public function testUseDebug() {
        $this->site->setConfigDirPath($this->getTestDirPath());
        $this->site->setConfigFileName('config-with-debug.php');
        $this->assertBoolAccessor([$this->site, 'isDebug'], true);
    }

    public function testIsMode() {
        $this->site->setConfigDirPath($this->getTestDirPath());
        $this->site->setConfigFileName('config-with-mode.php');

        $this->assertFalse($this->site->isProductionMode());
        $this->assertFalse($this->site->isDevMode());
        $this->assertFalse($this->site->isStagingMode());
        $this->assertTrue($this->site->isTestingMode());
        $this->assertFalse($this->site->isCustomMode());

        $this->site->setMode('production');

        $this->assertTrue($this->site->isProductionMode());
        $this->assertFalse($this->site->isDevMode());
        $this->assertFalse($this->site->isStagingMode());
        $this->assertFalse($this->site->isTestingMode());
        $this->assertFalse($this->site->isCustomMode());

        $this->site->setMode("foo");

        $this->assertFalse($this->site->isProductionMode());
        $this->assertFalse($this->site->isDevMode());
        $this->assertFalse($this->site->isStagingMode());
        $this->assertFalse($this->site->isTestingMode());
        $this->assertTrue($this->site->isCustomMode());
    }

    public function testConstructor_SettingProperties() {
        $this->assertEquals($this->site->getDirPath(), $this->site->getDirPath());
        $this->assertEquals('foo', $this->site->getName());
    }

    public function dataForDirectoryAccessors() {
        return [
            [
                'log',
            ],
            [
                'cache'
            ],
            [
                'upload',
            ],
            [
                'config',
            ],
        ];
    }

    /**
     * @dataProvider dataForDirectoryAccessors
     */
    public function testDirectoryAccessors($dirName) {
        $setter = 'set' . $dirName . 'DirPath';
        $getter = 'get' . $dirName . 'DirPath';
        $this->assertEquals(
            $this->getTestDirPath() . '/' . constant(strtoupper($dirName) . '_DIR_NAME'),
            $this->site->$getter()
        );

        $newDirPath = '/some/random/dir';
        $this->site->$setter($newDirPath);
        $this->assertEquals($newDirPath, $this->site->$getter());
    }

    public function testPublicDirPathAccessors() {
        $this->assertEquals(PUBLIC_DIR_PATH, $this->site->getPublicDirPath());
        $newPublicDirPath = '/new/public/dir';
        $this->site->setPublicDirPath($newPublicDirPath);
        $this->assertEquals($newPublicDirPath, $this->site->getPublicDirPath());
    }

    public function testDirPathAccessors() {
        $dirPath = 'foo/bar/baz';
        $this->site->setDirPath($dirPath);
        $this->assertEquals($dirPath, $this->site->getDirPath());
    }

    public function testNameAccessors() {
        $name = 'baz';
        $this->site->setName($name);
        $this->assertEquals($name, $this->site->getName());
    }

    public function dataForLoadConfigAndIsFallbackConfigUsed() {
        return [
            [
                true,
            ],
            [
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataForLoadConfigAndIsFallbackConfigUsed
     */
    public function testLoadConfigAndIsFallbackConfigUsed($isFallback) {
        $this->site->setConfigDirPath($this->getTestDirPath() . '/' . ($isFallback ? 'fallback' : ''));
        $config = $this->site->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertCount(2, $config);
        $this->assertEquals('some-value', $config['some-key']);
        $this->assertInstanceOf('ArrayIterator', $config['instance']);
        $this->assertEquals($isFallback, $this->site->isFallbackConfigUsed());
    }

    public function testIsFallbackConfigUsedThrowsExceptionWhenLoadConfigWasNotCalled() {
        $this->setExpectedException('\LogicException', 'The loadConfig() must be called first');
        $this->site->isFallbackConfigUsed();
    }

    public function testGetConfigFilePath() {
        $this->assertEquals($this->getTestDirPath() . '/' . CONFIG_DIR_NAME . '/' . Site::CONFIG_FILE_NAME, $this->site->getConfigFilePath());
    }
}