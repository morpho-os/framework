<?php
namespace MorphoTest\Web;

use Morpho\Core\Module;
use Morpho\Fs\File;
use Morpho\Test\TestCase;
use Morpho\Web\Host;
use Morpho\Web\Site;

class SiteTest extends TestCase {
    private $site;

    public function setUp() {
        parent::setUp();
        $this->site = new Site(null, $this->getTestDirPath());
    }

    public function testSubtyping() {
        $this->assertInstanceOf(Module::class, $this->site);
    }

    public function dataForDirectoryAccessors() {
        return [
            [
                LOG_DIR_NAME,
            ],
            [
                CACHE_DIR_NAME,
            ],
            [
                UPLOAD_DIR_NAME,
            ],
            [
                CONFIG_DIR_NAME,
            ],
        ];
    }

    /**
     * @dataProvider dataForDirectoryAccessors
     * Tests methods: set(log|cache|upload|config)DirPath() and respective reader.
     */
    public function testDirectoryAccessors($dirName) {
        $setter = 'set' . $dirName . 'DirPath';
        $getter = $dirName . 'DirPath';
        $this->assertEquals(
            $this->getTestDirPath() . '/' . constant(strtoupper($dirName) . '_DIR_NAME'),
            $this->site->$getter()
        );
        $newDirPath = '/some/random/dir';
        $this->assertNull($this->site->$setter($newDirPath));
        $this->assertEquals($newDirPath, $this->site->$getter());
    }

    public function testPublicDirPathAccessors() {
        $this->assertEquals(PUBLIC_DIR_PATH, $this->site->publicDirPath());
        $newPublicDirPath = '/new/public/dir';
        $this->assertNull($this->site->setPublicDirPath($newPublicDirPath));
        $this->assertEquals($newPublicDirPath, $this->site->publicDirPath());
    }

    public function testDirPathAccessors() {
        $dirPath = 'foo/bar/baz';
        $this->site->setDirPath($dirPath);
        $this->assertEquals($dirPath, $this->site->dirPath());
    }

    public function testHostAccessors() {
        $host = new Host('foo', 'bar');
        $this->assertNull($this->site->host());
        $this->assertInstanceOf(Site::class, $this->site->setHost($host));
        $this->assertSame($host, $this->site->host());
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

    public function testConfigAccessors() {
        $this->site->setConfigDirPath($this->getTestDirPath());
        $oldConfig = $this->site->config();
        $this->assertNotEmpty($oldConfig);
        $this->assertSame($oldConfig, $this->site->config());
        $newConfig = ['foo' => 'bar'];
        $this->assertNull($this->site->setConfig($newConfig));
        $this->assertSame($newConfig, $this->site->config());
    }

    public function testViewDirPathAccessors() {
        $oldViewDirPath = $this->site->viewDirPath();
        $this->assertNotEmpty($oldViewDirPath);

        $newViewDirPath = '/a/b/c';
        $this->assertNull($this->site->setViewDirPath($newViewDirPath));
        $this->assertSame($newViewDirPath, $this->site->viewDirPath());
    }

    public function testReadingConfigAfterWriting() {
        $configFilePath = $this->createTmpFile();
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $this->site->setConfigFilePath($configFilePath);
        $this->site->writeConfig($prevConfig);
        $this->assertEquals($prevConfig, $this->site->config());

        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $this->site->writeConfig($newConfig);
        $this->assertEquals($newConfig, $this->site->config());
    }

    public function testReloadConfig() {
        $configFilePath = $this->createTmpFile();
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $this->site->setConfigFilePath($configFilePath);
        File::writePhpVar($configFilePath, $prevConfig);
        $this->assertEquals($prevConfig, $this->site->config());

        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        File::writePhpVar($configFilePath, $newConfig);
        $this->assertEquals($prevConfig, $this->site->config());

        $this->site->reloadConfig();

        $this->assertEquals($newConfig, $this->site->config());
    }
}