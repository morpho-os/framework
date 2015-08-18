<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Site;

class SiteTest extends TestCase {
    public function setUp() {
        $this->site = new Site(['dirPath' => $this->getTestDirPath(), 'name' => 'foo']);
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

    public function testWebDirPathAccessors() {
        $this->assertEquals(WEB_DIR_PATH, $this->site->getWebDirPath());
        $newWebDirPath = '/new/web/dir';
        $this->site->setWebDirPath($newWebDirPath);
        $this->assertEquals($newWebDirPath, $this->site->getWebDirPath());
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