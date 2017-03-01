<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Site;

class SiteTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->site = new Site(['dirPath' => $this->getTestDirPath(), 'name' => 'foo']);
    }

    public function testConstructor_SettingProperties() {
        $this->assertEquals($this->site->dirPath(), $this->site->dirPath());
        $this->assertEquals('foo', $this->site->name());
    }

    public function dataForDirectoryAccessors() {
        return [
            [
                'log',
            ],
            [
                'cache',
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
        $getter = $dirName . 'DirPath';
        $this->assertEquals(
            $this->getTestDirPath() . '/' . constant(strtoupper($dirName) . '_DIR_NAME'),
            $this->site->$getter()
        );

        $newDirPath = '/some/random/dir';
        $this->site->$setter($newDirPath);
        $this->assertEquals($newDirPath, $this->site->$getter());
    }

    public function testPublicDirPathAccessors() {
        $this->assertEquals(PUBLIC_DIR_PATH, $this->site->publicDirPath());
        $newPublicDirPath = '/new/public/dir';
        $this->site->setPublicDirPath($newPublicDirPath);
        $this->assertEquals($newPublicDirPath, $this->site->publicDirPath());
    }

    public function testDirPathAccessors() {
        $dirPath = 'foo/bar/baz';
        $this->site->setDirPath($dirPath);
        $this->assertEquals($dirPath, $this->site->dirPath());
    }

    public function testNameAccessors() {
        $name = 'baz';
        $this->site->setName($name);
        $this->assertEquals($name, $this->site->name());
    }

    public function dataForFallbackConfigUsed() {
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
     * @dataProvider dataForFallbackConfigUsed
     */
    public function testFallbackConfigUsed($shouldBeUsed) {
        $this->site->setConfigDirPath($this->getTestDirPath() . '/' . ($shouldBeUsed ? 'fallback' : ''));
        $config = $this->site->config();
        $this->assertInternalType('array', $config);
        $this->assertCount(2, $config);
        $this->assertEquals('some-value', $config['some-key']);
        $this->assertInstanceOf('ArrayIterator', $config['instance']);
        $this->assertEquals($shouldBeUsed, $this->site->fallbackConfigUsed());
    }

    public function testConfigFilePath() {
        $this->assertEquals($this->getTestDirPath() . '/' . CONFIG_DIR_NAME . '/' . Site::CONFIG_FILE_NAME, $this->site->configFilePath());
    }
}