<?php
namespace System\Controller;

use Morpho\Di\IServiceManagerAware;
use Morpho\Web\Controller;
use Morpho\Code\CodeTool;
use Morpho\Core\ModuleManager;
use Morpho\Db\Db;
use Morpho\Web\Session;

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
            $this->install($dbConfig, $dropTables);
            $res = ['redirect' => true];
        } catch (\Exception $e) {
            return $this->error((string) $e);
        }

        return $this->success($res);
    }

    public function checkEnvAction() {
        try {
            $this->tryInitSession();
        } catch (\Exception $e) {
            return $this->error((string) $e);
        }
        return $this->success();
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
        $db = new Db($dbConfig);

        // Check that we can connect and make queries.
        $db->listTables();

        if ($dropTables) {
            $db->deleteAllTables();
        }

        // Set the new DB instance for all services.
        $this->serviceManager->get('settingManager')
            ->setDb($db);

        $this->installCore($db);
        $this->installModules($db);
        $this->saveSiteConfig($dbConfig);

        return true;
    }

    protected function installCore(Db $db) {
        $tableDefinitions = [
            /*
            'file' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
                    'path' => [
                        'type' => 'varchar',
                    ],
                    'type' => [
                        'type' => 'varchar',
                        'length' => 10,
                    ],
                ],
                'indexes' => [
                    'path',
                    'type',
                ],
            ],
            */
            'module' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk'
                    ],
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'status' => [
                        'type' => 'int',
                    ],
                    'weight' => [
                        'type' => 'int',
                    ],
                ],
                'indexes' => [
                    'name',
                ],
            ],
            //'controller' =>
            'event' => [
                'columns' => [
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'priority' => [
                        'type' => 'integer',
                    ],
                    'method' => [
                        'type' => 'varchar',
                    ],
                    'moduleId' => [
                        'type' => 'integer',
                        'unsigned' => true,
                    ],
                ],
                'fks' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ],
                ],
            ],
            'setting' => [
                'columns' => [
                    'id' => [
                        'type' => 'pk',
                    ],
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'value' => [
                        'type' => 'text',
                    ],
                    'moduleId' => [
                        'type' => 'int',
                        'unsigned' => 'true',
                    ],
                ],
                'fks' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ]
                ],
            ],
        ];
        $db->createTables($tableDefinitions);
    }

    protected function installModules(Db $db) {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $modules = $moduleManager->listModules(ModuleManager::UNINSTALLED);
        $moduleManager->setDb($db);
        foreach ($modules as $moduleName) {
            $moduleManager->installModule($moduleName);
            $moduleManager->enableModule($moduleName);
        }

        $serviceManager = $this->serviceManager;
        $serviceManager->set('db', $db);
        $serviceManager->get('siteManager')->isFallbackMode(false);
        $router = $this->serviceManager->createRouterService();
        if ($router instanceof IServiceManagerAware) {
            $router->setServiceManager($serviceManager);
        }
        $router->rebuildRoutes(MODULE_DIR_PATH);
    }

    protected function saveSiteConfig(array $dbConfig) {
        $site = $this->serviceManager->get('siteManager')->getCurrentSite();
        $config = $site->getConfig();
        $config['db'] = $dbConfig;
        $configFilePath = $site->getConfigFilePath();
        CodeTool::varToPhp($config, $configFilePath);
        #chmod($configFilePath, 0440);
    }

    private function tryInitSession() {
        new Session(__METHOD__);
    }
}

