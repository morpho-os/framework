<?php
namespace Morpho\Core;

use Morpho\Base\Environment;
use Morpho\Di\ServiceManager as BaseServiceManager;
use Morpho\Db\Db;

abstract class ServiceManager extends BaseServiceManager {
    protected $config;

    public function __construct(array $config = null, array $services = null) {
        parent::__construct($services);
        $this->config = $config;
        $this->setAliases(['dispatcher' => 'modulemanager']);
    }

    protected function createEnvironmentService() {
        return new Environment();
    }

    protected function createDbService() {
        $dbConfig = $this->config['db'];
        return new Db(isset($dbConfig['dsn']) ? $dbConfig['dsn'] : $dbConfig);
    }

    abstract protected function createModuleManagerService();

    protected function createModuleClassLoaderService() {
        $config = $this->config;
        $classLoader = new ModuleClassLoader(
            MODULE_DIR_PATH,
            $config['cacheDirPath'],
            $config['moduleClassLoader']['useCache']
        );
        return $classLoader;
    }

    protected function createViewService() {
        return $this->get('moduleManager')
            ->get($this->config['view']);
    }

    protected function createSettingManagerService() {
        return new SettingManager($this->get('db'));
    }
}
