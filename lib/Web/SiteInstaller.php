<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Db\Sql\Db;
use Morpho\Di\IWithServiceManager;
use Morpho\Di\TWithServiceManager;
use Zend\Stdlib\ArrayUtils;

class SiteInstaller implements IWithServiceManager {
    use TWithServiceManager;

    private const HOME_PAGE_URI = '/system/module/list';

    protected $site;

    public function __construct(Site $site) {
        $this->site = $site;
    }

    public function reinstall(array $newSiteConfig, bool $dropTables): void {
        if ($this->isInstalled()) {
            $this->resetToInitialState();
        }
        $this->install($newSiteConfig, $dropTables);
    }

    public function resetToInitialState(): void {
        $this->site->fs()->deleteConfigFile();
    }

    public function isInstalled(): bool {
        return $this->site->isFallbackMode() === false
            && $this->site->fs()->canLoadConfigFile();
    }

    public function install(array $newSiteConfig, bool $dropTables): void {
        $newSiteConfig = ArrayUtils::merge($this->site->config(), $newSiteConfig);

        $servicesConfig = $newSiteConfig['services'];
        $moduleNames = function () use ($newSiteConfig): array {
            $modules = $newSiteConfig['modules'] ?? [];
            $moduleNames = [];
            foreach ($modules as $name => $config) {
                if (is_numeric($name)) {
                    $moduleNames[] = $config;
                } else {
                    $moduleNames[] = $name;
                }
            }
            return $moduleNames;
        };

        $db = self::newDbConnection($servicesConfig['db']);

        $schemaManager = $db->schemaManager();

        if ($dropTables) {
            $schemaManager->deleteAllTables();
        } else {
            // Check that we can connect and make queries.
            $schemaManager->tableNames();
        }

        $serviceManager = $this->serviceManager;

        $site = $this->site;
        $site->isFallbackMode(false);
        $site->setConfig($newSiteConfig);

        $serviceManager->setConfig($servicesConfig);

        $serviceManager->get('settingsManager')->setDb($db);
        $serviceManager->set('db', $db);

        $this->installModules(
            $db,
            $this->serviceManager->get('moduleManager'),
            $moduleNames()
        );

        $services = ['site' => $site];
        $newServiceManager = $serviceManager->get('app')->newServiceManager($services);
        $newServiceManager->setConfig($servicesConfig);

        $this->setPageHandlers($newServiceManager);
        $this->initRoutes($newServiceManager);

        $site->fs()->writeConfig($newSiteConfig);
    }

    protected static function initRoutes($serviceManager): void {
        $router = $serviceManager->newRouterService();
        if ($router instanceof IWithServiceManager) {
            $router->setServiceManager($serviceManager);
        }
        $router->rebuildRoutes();
    }

    protected static function installModules(Db $db, ModuleManager $moduleManager, iterable $modules): void {
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
                $db->eval($db->query()->useDb($dbName));
                return $db;
            } else {
                throw $e;
            }
        }
    }
}