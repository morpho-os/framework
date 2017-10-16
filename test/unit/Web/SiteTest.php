<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Web\SiteConfig;
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

    public function testConfig() {
        $site = $this->newSite($this->createMock(SitePathManager::class));
        $this->assertInstanceOf(SiteConfig::class, $site->config());
    }

    public function testConfig_AfterSettingNewPathManager() {
        $site = $this->newSite($this->createMock(SitePathManager::class));
        $oldConfig = $site->config();

        $site->setPathManager($this->createMock(SitePathManager::class));
        $newConfig = $site->config();

        $this->assertNotSame($oldConfig, $newConfig);
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
}