<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use const Morpho\Core\VENDOR;
use Morpho\Test\TestCase;
use Morpho\Core\Module;
use Morpho\Web\Site;
use Morpho\Web\SiteFs;
use Morpho\Web\View\IHasTheme;
use Morpho\Web\View\THasTheme;

class SiteTest extends TestCase {
    public function testGettersOfConstructorParams() {
        $fs = $this->createMock(SiteFs::class);
        $moduleName = VENDOR . '/localhost';
        $hostName = 'example.com';
        $site = new Site($moduleName, $fs, $hostName);
        $this->assertSame($moduleName, $site->name());
        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($fs, $site->fs());
    }

    public function dataForConfig_FallbackAndNotFallbackMode() {
        $config = [
            'some-key' => 'some-value',
            'instance' => new \ArrayIterator([]),
        ];
        return [
            [
                $this->createConfiguredMock(SiteFs::class, [
                    'canLoadConfigFile' => false,
                    'loadFallbackConfigFile' => $config
                ]),
                $config,
                true,
            ],
            [
                $this->createConfiguredMock(SiteFs::class, [
                    'canLoadConfigFile' => true,
                    'loadConfigFile' => $config,
                ]),
                $config,
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataForConfig_FallbackAndNotFallbackMode
     */
    public function testConfig_FallbackAndNotFallbackMode($fs, array $config, bool $isFallbackMode) {
        $site = $this->newSite($fs);

        $actual = $site->config();

        $this->assertSame($config, $actual);
        $this->checkBoolAccessor([$site, 'isFallbackMode'], $isFallbackMode);
    }

    public function testConfig_Accessors() {
        $site = $this->newSite($this->createMock(SiteFs::class));
        $newConfig = ['foo' => 'bar'];
        $this->assertNull($site->setConfig($newConfig));
        $this->assertSame($newConfig, $site->config());
    }

    public function testConfig_AfterSettingNewFs() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $fs = $this->createConfiguredMock(SiteFs::class, [
            'canLoadConfigFile' => true,
            'loadConfigFile' => $prevConfig,
        ]);
        $site = $this->newSite($fs);
        
        $this->assertSame($prevConfig, $site->config());
        
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $fs = $this->createConfiguredMock(SiteFs::class, [
            'canLoadConfigFile' => true,
            'loadConfigFile' => $newConfig,
        ]);
        $site->setFs($fs);
        
        $this->assertSame($newConfig, $site->config());
    }

    public function testReadingConfigAfterWriting() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $fs = $this->createConfiguredMock(SiteFs::class, [
            'canLoadConfigFile' => true,
        ]);
        $fs->expects($this->exactly(2))
            ->method('loadConfigFile')
            ->willReturnOnConsecutiveCalls($prevConfig, $newConfig);
        $fs->expects($this->exactly(2))
            ->method('writeConfig');

        $site = $this->newSite($fs);

        $site->writeConfig($prevConfig);

        $this->assertSame($prevConfig, $site->config());

        $site->writeConfig($newConfig);

        $this->assertSame($newConfig, $site->config());
    }

    public function testReloadConfig() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $fs = $this->createConfiguredMock(SiteFs::class, [
            'canLoadConfigFile' => true,
        ]);
        $fs->expects($this->exactly(2))
            ->method('loadConfigFile')
            ->willReturnOnConsecutiveCalls($prevConfig, $newConfig);
        $site = $this->newSite($fs);

        $this->assertSame($prevConfig, $site->config());

        $this->assertSame($prevConfig, $site->config());

        $this->assertSame($newConfig, $site->reloadConfig());

        $this->assertSame($newConfig, $site->config());
    }

    public function testSiteIsAModuleAndWithTheme() {
        $fs = $this->createConfiguredMock(SiteFs::class, []);
        $site = new class(VENDOR . '/foo', $fs, 'localhost') extends Site implements IHasTheme {
            use THasTheme;
        };
        $this->assertInstanceOf(Module::class, $site);
    }

    private function newSite($fs) {
        return new Site(VENDOR . '/foo', $fs, 'localhost');
    }
}