<?php
namespace Morpho\System\Controller;

use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Controller;
use Morpho\Db\Sql\Db;
use Morpho\Fs\File;

class InstallController extends Controller {
    public function indexAction() {
        return [
            'dbConfig' => $this->serviceManager->get('siteManager')->currentSiteConfig()['db'],
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
            $success = $this->install($dbConfig, $dropTables);
            if ($success) {
                return $this->success(['redirect' => true]);
            }
        } catch (\Exception $e) {
            return $this->error((string) $e);
        }
        return $this->error();
    }

    protected function beforeEach(): void {
        parent::beforeEach();
        if ($this->isInstalled()) {
            $this->accessDenied();
        }
        $this->setLayout('install');
    }

    protected function isInstalled(): bool {
        return $this->serviceManager->get('siteManager')->isFallbackMode() === false;
    }

    protected function install(array $dbConfig, bool $dropTables): bool {
        $schemaManager = null;
        try {
            $db = new Db($dbConfig);
        } catch (\PDOException $e) {
            if (false !== stripos($e->getMessage(), 'SQLSTATE[HY000] [1049] Unknown database')) {
                $dbName = $dbConfig['db'];
                $dbConfig['db'] = '';
                $db = new Db($dbConfig);
                $schemaManager = $db->schemaManager();
                $schemaManager->createDatabase($dbName);
                $db->eval($db->query()->useDb($dbName));
                $dbConfig['db'] = $dbName;
            } else {
                throw $e;
            }
        }

        if (null === $schemaManager) {
            $schemaManager = $db->schemaManager();
        }

        // Check that we can connect and make queries.
        $schemaManager->tableNames();

        if ($dropTables) {
            $schemaManager->deleteAllTables();
        }

        // Set the new DB instance for all services.
        $this->serviceManager->get('settingManager')
            ->setDb($db);

        $this->initNewEnv($db);

        $this->installModules($db);

        $this->initRoutes();

        $this->saveSiteConfig($dbConfig);

        return true;
    }

    protected function initRoutes() {
        $serviceManager = $this->serviceManager;
        $router = $serviceManager->createRouterService();
        if ($router instanceof IServiceManagerAware) {
            $router->setServiceManager($serviceManager);
        }
        $router->rebuildRoutes();
    }

    protected function installModules(Db $db) {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modules = $this->serviceManager->get('siteManager')->currentSiteConfig()['modules']
            ?? $moduleManager->uninstalledModuleNames();
        $moduleManager->setDb($db);
        foreach ($modules as $moduleName) {
            $moduleManager->installModule($moduleName);
            $moduleManager->enableModule($moduleName);
        }
        $moduleManager->isFallbackMode(false);
    }

    protected function initNewEnv(Db $db) {
        $serviceManager = $this->serviceManager;
        $serviceManager->set('db', $db);
        $serviceManager->get('siteManager')->isFallbackMode(false);
    }

    protected function saveSiteConfig(array $dbConfig) {
        $site = $this->serviceManager->get('siteManager')->currentSite();
        $config = $site->config();
        $config['db'] = $dbConfig;
        $configFilePath = $site->configFilePath();
        File::writePhpVar($configFilePath, $config);
        #chmod($configFilePath, 0440);
    }
}

