<?php
namespace Morpho\System\Controller;

use Morpho\Di\IServiceManagerAware;
use Morpho\Web\ServiceManager;
use Morpho\System\Module as SystemModule;
use Morpho\Web\Controller;
use Morpho\Db\Sql\Db;
use Morpho\Fs\File;
use Morpho\Web\ModuleManager;
use Morpho\Web\Request;

class InstallController extends Controller {
    public function indexAction() {
        return [
            'dbConfig' => $this->serviceManager->get('site')->config()['db'],
        ];
    }

    public function installAction() {
        $dbConfig = $this->args();

        $dbConfig += [
            'password' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
        ];
        $dbConfig['driver'] = 'mysql';

        $dropTables = !empty($dbConfig['dropTables']);
        unset($dbConfig['dropTables']);

        $res = null;
        try {
            $this->install($dbConfig, $dropTables);
            return $this->success(['redirect' => true]);
        } catch (\Exception $e) {
            return $this->error((string) $e);
        }
    }

    protected function beforeEach(): void {
        parent::beforeEach();
        if ($this->isInstalled()) {
            $this->accessDenied();
        }
        $this->setLayout('install');
    }

    protected function isInstalled(): bool {
        return $this->serviceManager->get('site')->isFallbackMode() === false;
    }

    protected function install(array $dbConfig, bool $dropTables) {
        $db = $this->newDbConnection($dbConfig);

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
        $site = $serviceManager->get('site');
        $site->isFallbackMode(false);
        $newSiteConfig = $site->config();
        $newSiteConfig['db'] = $dbConfig;
        $site->setConfig($newSiteConfig);

        $this->installModules($db, $this->serviceManager->get('moduleManager'), $newSiteConfig);
        $newServiceManager = $serviceManager->get('app')->newServiceManager($site);
        $this->setPageHandlers($newServiceManager);
        $this->initRoutes($newServiceManager);
        File::writePhpVar($site->configFilePath(), $newSiteConfig);
        #chmod($configFilePath, 0440);
    }

    protected function initRoutes(ServiceManager $serviceManager): void {
        $router = $serviceManager->newRouterService();
        if ($router instanceof IServiceManagerAware) {
            $router->setServiceManager($serviceManager);
        }
        $router->rebuildRoutes();
    }

    protected function installModules(Db $db, $moduleManager, array $siteConfig): void {
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

    protected function setPageHandlers(ServiceManager $serviceManager): void {
        $serviceManager->get('settingsManager')
            ->set(
                Request::HOME_HANDLER,
                [
                    'handler' => [ModuleManager::SYSTEM_MODULE, 'Module', 'index'],
                    'uri' => SystemModule::HOME_PAGE_URI
                ],
                ModuleManager::SYSTEM_MODULE
            );
    }

    protected function newDbConnection(array $dbConfig): Db {
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