<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Db\Sql\Db;
use Morpho\Di\IServiceManagerAware;
use Morpho\Di\TWithServiceManager;
use Morpho\Fs\File;
use Morpho\Di\ServiceManager;

class SiteInstaller implements IServiceManagerAware {
    use TWithServiceManager;

    private const HOME_PAGE_URI = '/system/module/list';

    protected $site;

    public function __construct(ISite $site) {
        $this->site = $site;
    }

    public function reinstall(array $newDbConfig, bool $dropTables): void {
        if ($this->isInstalled()) {
            $this->resetToStartState();
        }
        $this->install($newDbConfig, $dropTables);
    }

    public function resetToInitialState(): void {
        $configFilePath = $this->site->configFilePath();
        if (is_file($configFilePath)) {
            unlink($configFilePath);
        }
    }

    public function isInstalled(): bool {
        return $this->site->isFallbackMode() === false
            && is_file($this->site->configFilePath());
    }

    public function install(array $newDbConfig, bool $dropTables): void {
        $db = self::newDbConnection($newDbConfig);

        $schemaManager = $db->schemaManager();

        if ($dropTables) {
            $schemaManager->deleteAllTables();
        } else {
            // Check that we can connect and make queries.
            $schemaManager->tableNames();
        }

        $serviceManager = $this->serviceManager;
        $serviceManager->get('settingsManager')->setDb($db);
        $serviceManager->set('db', $db);
        $site = $this->site;
        $site->isFallbackMode(false);
        $newSiteConfig = $site->config();
        $newSiteConfig['db'] = $newDbConfig;
        $site->setConfig($newSiteConfig);

        $this->installModules($db, $this->serviceManager->get('moduleManager'), $newSiteConfig);
        $newServiceManager = $serviceManager->get('app')->newServiceManager($site);
        $this->setPageHandlers($newServiceManager);
        $this->initRoutes($newServiceManager);
        File::writePhpVar($this->site->configFilePath(), $newSiteConfig);
    }

    protected static function initRoutes(ServiceManager $serviceManager): void {
        $router = $serviceManager->newRouterService();
        if ($router instanceof IServiceManagerAware) {
            $router->setServiceManager($serviceManager);
        }
        $router->rebuildRoutes();
    }

    protected static function installModules(Db $db, $moduleManager, array $siteConfig): void {
        $modules = $siteConfig['modules'] ?? [];
        if (empty($modules)) {
            $modules = $moduleManager->uninstalledModuleNames();
        }
        $moduleManager->setDb($db);
        foreach ($modules as $moduleName) {
            $moduleManager->installModule($moduleName);
            $moduleManager->enableModule($moduleName);
        }
    }

    protected static function setPageHandlers(ServiceManager $serviceManager): void {
        $serviceManager->get('settingsManager')
            ->set(
                Request::HOME_HANDLER,
                [
                    'handler' => [ModuleManager::SYSTEM_MODULE, 'Module', 'index'],
                    'uri' => self::HOME_PAGE_URI
                ],
                ModuleManager::SYSTEM_MODULE
            );
    }

    protected static function newDbConnection(array $dbConfig): Db {
        try {
            return Db::connect($dbConfig);
        } catch (\PDOException $e) {
            if (false !== stripos($e->getMessage(), 'SQLSTATE[HY000] [1049] Unknown database')) {
                $dbName = $dbConfig['db'];
                $dbConfig['db'] = '';
                $db = Db::connect($dbConfig);
                $schemaManager = $db->schemaManager();
                $schemaManager->createDatabase($dbName);
                $db->eval($db->newQuery()->useDb($dbName));
                return $db;
            } else {
                throw $e;
            }
        }
    }
}