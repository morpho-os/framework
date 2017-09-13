<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Web;

use Morpho\Core\ModuleManager;
use Morpho\Core\SettingsManager;
use Morpho\Di\ServiceManager;
use Morpho\Test\DbTestCase;
use Morpho\Web\Application;
use Morpho\Web\Site;
use Morpho\Web\SiteInstaller;

class SiteInstallerTest extends DbTestCase {
    public function testApi() {
        $siteDirPath = $this->createTmpDir();

        $configDirPath = $siteDirPath . '/config';
        mkdir($configDirPath, 0777, true);

        copy($this->getTestDirPath() . '/fallback.php', $configDirPath . '/fallback.php');

        $site = new Site('foo/bar', $siteDirPath, 'localhost');

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
        $configFilePath = $configDirPath . '/config.php';

        $this->assertFalse($siteInstaller->isInstalled());
        $this->assertFalse(is_file($configFilePath));

        $this->assertNull($siteInstaller->install($newDbConfig, true));

        $this->assertTrue($siteInstaller->isInstalled());
        $this->assertTrue(is_file($configFilePath));

        $this->assertNull($siteInstaller->resetToInitialState());

        $this->assertFalse($siteInstaller->isInstalled());
        $this->assertFalse(is_file($configFilePath));
    }
}