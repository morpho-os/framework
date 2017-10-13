<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Web\ModuleManager;
use Morpho\Core\SettingsManager;
use Morpho\Web\ServiceManager;
use Morpho\Test\DbTestCase;
use Morpho\Web\Application;
use Morpho\Web\Site;
use Morpho\Web\SiteFs;
use Morpho\Web\SiteInstaller;

class SiteInstallerTest extends DbTestCase {
    public function testApi() {
        $fs = new class($this->sut()->siteConfig($this->dbConfig())) extends SiteFs {
            private $state;
            private $config;

            private const CONFIG_DELETED = 1;
            private const INSTALLED = 2;

            public function __construct(array $config) {
                $this->config = $config;
            }

            public function deleteConfigFile(): void {
                $this->state = self::CONFIG_DELETED;
                $this->config = null;
            }

            public function canLoadConfigFile(): bool {
                return $this->state === self::INSTALLED;
            }

            public function writeConfig(array $config): void {
                $this->state = self::INSTALLED;
                $this->config = $config;
            }

            public function loadConfigFile(): array {
                return $this->config;
            }

            public function loadFallbackConfigFile(): array {
                return $this->config;
            }
        };

        $site = new Site('foo/bar', $fs, 'localhost');

        $oldServiceManager = new ServiceManager();
        $oldServiceManager->set('settingsManager', $this->createMock(SettingsManager::class));
        $moduleManager = $this->createMock(ModuleManager::class);
        $moduleManager->expects($this->atLeastOnce())
            ->method('installModule');
        $oldServiceManager->set('moduleManager', $moduleManager);
        $newServiceManager = new class extends ServiceManager {
            public function newRouterService() {
                return new class {
                    public function rebuildRoutes() {
                    }
                };
            }
        };
        $app = $this->createMock(Application::class);
        $app->expects($this->atLeastOnce())
            ->method('newServiceManager')
            ->will($this->returnValue($newServiceManager));
        $oldServiceManager->set('app', $app);
        $newServiceManager->set('settingsManager', $this->createMock(SettingsManager::class));

        $siteInstaller = (new SiteInstaller($site))
            ->setServiceManager($oldServiceManager);

        $newDbConfig = $this->dbConfig();

        $this->assertFalse($siteInstaller->isInstalled());

        $this->assertNull($siteInstaller->install(['services' => ['db' => $newDbConfig]], true));

        $this->assertTrue($siteInstaller->isInstalled());

        $this->assertNull($siteInstaller->resetToInitialState());

        $this->assertFalse($siteInstaller->isInstalled());
    }
}