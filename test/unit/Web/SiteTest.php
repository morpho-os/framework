<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use const Morpho\Core\CACHE_DIR_NAME;
use const Morpho\Core\CONFIG_DIR_NAME;
use const Morpho\Core\LOG_DIR_NAME;
use const Morpho\Core\VENDOR;
use Morpho\Web\ISite;
use const Morpho\Web\PUBLIC_DIR_PATH;
use Morpho\Web\Theme;
use Morpho\Fs\File;
use Morpho\Test\TestCase;
use Morpho\Web\Site;
use const Morpho\Web\UPLOAD_DIR_NAME;

class SiteTest extends TestCase {
    /**
     * @var Site
     */
    private $site;

    private $moduleName = VENDOR . '/localhost';

    private $hostName = 'example.com';

    public function setUp() {
        parent::setUp();
        $this->site = new Site($this->moduleName, $this->getTestDirPath(), $this->hostName);
    }

    public function testGettersOfConstructorParams() {
        $this->assertEquals($this->moduleName, $this->site->name());
        $this->assertEquals($this->getTestDirPath(), $this->site->dirPath());
        $this->assertEquals($this->hostName, $this->site->hostName());
    }

    public function dataForDirPathAccessors() {
        $testDirPath = $this->getTestDirPath();
        return [
            [
                $testDirPath . '/' . LOG_DIR_NAME,
                LOG_DIR_NAME,
            ],
            [
                $testDirPath . '/' . CACHE_DIR_NAME,
                CACHE_DIR_NAME,
            ],
            [
                $testDirPath . '/' . UPLOAD_DIR_NAME,
                UPLOAD_DIR_NAME,
            ],
            [
                $testDirPath . '/' . CONFIG_DIR_NAME,
                CONFIG_DIR_NAME,
            ],
        ];
    }

    /**
     * @dataProvider dataForDirPathAccessors
     * Tests methods: set(log|cache|upload|config)DirPath() and respective reader.
     */
    public function testDirPathAccessors($expectedDirPath, $dirName) {
        $setter = 'set' . $dirName . 'DirPath';
        $getter = $dirName . 'DirPath';
        $this->assertEquals(
            $expectedDirPath,
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

    public function dataForIsFallbackMode() {
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
     * @dataProvider dataForIsFallbackMode
     */
    public function testIsFallbackMode($shouldBeUsed) {
        $this->site->setConfigDirPath($this->getTestDirPath() . '/' . ($shouldBeUsed ? 'fallback' : ''));
        $config = $this->site->config();
        $this->assertInternalType('array', $config);
        $this->assertCount(2, $config);
        $this->assertEquals('some-value', $config['some-key']);
        $this->assertInstanceOf('ArrayIterator', $config['instance']);

        $this->checkBoolAccessor([$this->site, 'isFallbackMode'], $shouldBeUsed);
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

    public function testPublicDirAccessors() {
        $this->markTestIncomplete();
    }
    
    public function testModuleCanBeSiteAndTheme() {
        $module = new class ('foo', $this->getTestDirPath()) extends Theme implements ISite {

            public function hostName(): ?string {
            }

            public function setCacheDirPath(string $dirPath): void {
            }

            public function cacheDirPath(): string {
            }

            public function setConfigDirPath(string $dirPath): void {
            }

            public function configDirPath(): string {
            }

            public function setLogDirPath(string $dirPath): void {
            }

            public function logDirPath(): string {
            }

            public function setUploadDirPath(string $dirPath): void {
            }

            public function uploadDirPath(): string {
            }

            public function setTmpDirPath(string $dirPath): void {
            }

            public function tmpDirPath(): string {
            }

            public function setPublicDirPath(string $dirPath): void {
            }

            public function publicDirPath(): string {
            }

            public function useOwnPublicDir(): void {
            }

            public function useCommonPublicDir(): void {
            }

            public function usesOwnPublicDir(): bool {
            }

            public function setConfigFilePath(string $filePath): void {
            }

            public function configFilePath(): string {
            }

            public function setConfigFileName(string $fileName): void {
            }

            public function configFileName(): string {
            }

            public function setConfig(array $config): void {
            }

            public function config(): array {
            }

            public function reloadConfig(): void {
            }

            public function writeConfig(array $config): void {
            }

            public function isFallbackMode(bool $flag = null): bool {
            }

            public function fallbackConfigFilePath(): string {
            }
        };
        $this->assertInstanceOf(Theme::class, $module);
        $this->assertInstanceOf(ISite::class, $module);
    }
}