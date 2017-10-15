<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use const Morpho\Core\VENDOR;
use Morpho\Test\TestCase;
use Morpho\Web\Module;
use Morpho\Web\Site;
use Morpho\Web\SitePathManager;
use Morpho\Web\View\IHasTheme;
use Morpho\Web\View\THasTheme;

class SiteTest extends TestCase {
    public function testGettersOfConstructorParams() {
        $pathManager = $this->createMock(SitePathManager::class);
        $moduleName = VENDOR . '/localhost';
        $hostName = 'example.com';
        $site = new Site($moduleName, $pathManager, $hostName);
        $this->assertSame($moduleName, $site->name());
        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($pathManager, $site->pathManager());
    }

    public function testConfig_Accessors() {
        $site = $this->newSite($this->createMock(SitePathManager::class));
        $newConfig = ['foo' => 'bar'];
        $this->assertNull($site->setConfig($newConfig));
        $this->assertSame($this->normalizedConfig($newConfig), $site->config());
    }

    public function testConfig_AfterSettingNewPathManager() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $pathManager = $this->createConfiguredMock(SitePathManager::class, [
            'canLoadConfigFile' => true,
            'loadConfigFile' => $prevConfig,
        ]);
        $site = $this->newSite($pathManager);
        
        $this->assertSame($this->normalizedConfig($prevConfig), $site->config());
        
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $pathManager = $this->createConfiguredMock(SitePathManager::class, [
            'canLoadConfigFile' => true,
            'loadConfigFile' => $newConfig,
        ]);
        $site->setPathManager($pathManager);
        
        $this->assertSame($this->normalizedConfig($newConfig), $site->config());
    }

    public function testReadingConfigAfterWriting() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $pathManager = $this->createConfiguredMock(SitePathManager::class, [
            'canLoadConfigFile' => true,
        ]);
        $pathManager->expects($this->exactly(2))
            ->method('loadConfigFile')
            ->willReturnOnConsecutiveCalls($prevConfig, $newConfig);
        $pathManager->expects($this->exactly(2))
            ->method('writeConfig');

        $site = $this->newSite($pathManager);

        $site->writeConfig($prevConfig);

        $this->assertSame($this->normalizedConfig($prevConfig), $site->config());

        $site->writeConfig($newConfig);

        $this->assertSame($this->normalizedConfig($newConfig), $site->config());
    }

    public function testReloadConfig() {
        $prevConfig = ['foo' => 'bar', 'ee4299e7aa2c0f9e6b924967fd142582'];
        $newConfig = ['foo' => 'bar', '90fbc3240ee8d41e81cdb9ca38977116'];
        $pathManager = $this->createConfiguredMock(SitePathManager::class, [
            'canLoadConfigFile' => true,
        ]);
        $pathManager->expects($this->exactly(2))
            ->method('loadConfigFile')
            ->willReturnOnConsecutiveCalls($prevConfig, $newConfig);
        $site = $this->newSite($pathManager);

        $prevNormalizedConfig = $this->normalizedConfig($prevConfig);
        $newNormalizedConfig = $this->normalizedConfig($newConfig);

        $this->assertSame($prevNormalizedConfig, $site->config());
        $this->assertSame($prevNormalizedConfig, $site->config()); // two calls consistently must return the same result

        $this->assertSame($newNormalizedConfig, $site->reloadConfig());
        $this->assertSame($newNormalizedConfig, $site->config());
    }

    public function testSiteIsAModuleAndWithTheme() {
        $pathManager = $this->createConfiguredMock(SitePathManager::class, []);
        $site = new class(VENDOR . '/foo', $pathManager, 'localhost') extends Site implements IHasTheme {
            use THasTheme;
        };
        $this->assertInstanceOf(Module::class, $site);
    }

    private function newSite($pathManager) {
        return new Site(VENDOR . '/foo', $pathManager, 'localhost');
    }

    private function normalizedConfig(array $config): array {
        return $config + ['modules' => []];
    }
}