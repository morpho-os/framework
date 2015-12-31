<?php
namespace System\Controller;

use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Controller;
use Morpho\Code\CodeTool;
use Morpho\Core\ModuleManager;
use Morpho\Db\Db;

class InstallController extends Controller {
    public function installAction() {
        $dbConfig = $this->getArgs();

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

    protected function beforeEach() {
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
        try {
            $db = new Db($dbConfig);
        } catch (\PDOException $e) {
            if (false !== stripos($e->getMessage(), 'SQLSTATE[HY000] [1049] Unknown database')) {
                $dbName = $dbConfig['db'];
                $dbConfig['db'] = '';
                $db = new Db($dbConfig);
                $db->createDatabase($dbName);
                $db->useDatabase($dbName);
                $dbConfig['db'] = $dbName;
            }
        }

        // Check that we can connect and make queries.
        $db->listTables();

        if ($dropTables) {
            $db->deleteAllTables();
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
        $router = $this->serviceManager->createRouterService();
        if ($router instanceof IServiceManagerAware) {
            $router->setServiceManager($this->serviceManager);
        }
        $router->rebuildRoutes();
    }

    protected function installModules(Db $db) {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modules = $moduleManager->listModules(ModuleManager::UNINSTALLED);
        $moduleManager->setDb($db);
        foreach ($modules as $moduleName) {
            $moduleManager->installModule($moduleName);
            $moduleManager->enableModule($moduleName);
        }
    }

    protected function initNewEnv(Db $db) {
        $serviceManager = $this->serviceManager;
        $serviceManager->set('db', $db);
        $serviceManager->get('siteManager')->isFallbackMode(false);
    }

    protected function saveSiteConfig(array $dbConfig) {
        $site = $this->serviceManager->get('siteManager')->getCurrentSite();
        $config = $site->getConfig();
        $config['db'] = $dbConfig;
        $configFilePath = $site->getConfigFilePath();
        CodeTool::writeVarToFile($config, $configFilePath);
        #chmod($configFilePath, 0440);
    }
}

