<?php
namespace Morpho\Core;

use Morpho\Base\Environment;
use Morpho\Di\ServiceManager as BaseServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\Db\Db;
use Morpho\Di\IServiceManagerAware;

abstract class ServiceManager extends BaseServiceManager {
    protected $config;

    public function __construct(array $config, $services = null) {
        if (null !== $services) {
            foreach ($services as $id => $service) {
                $this->set($id, $service);
            }
        }
        $this->config = $config;
        $this->setAliases(['dispatcher' => 'modulemanager']);
    }

    protected function createEnvironmentService() {
        return new Environment();
    }

    protected function createErrorHandlerService() {
        return new ErrorHandler();
    }

    protected function createDbService() {
        return new Db($this->config['db']);
    }

    abstract protected function createModuleManagerService();

    protected function createModuleAutoloaderService() {
        $config = $this->config;
        return new ModuleAutoloader(
            MODULE_DIR_PATH,
            $config['cacheDirPath'],
            $config['moduleAutoloader']['useCache']
        );
    }

    protected function createViewService() {
        return $this->get('moduleManager')
            ->get($this->config['view']);
    }

    protected function createSettingManagerService() {
        return new SettingManager($this->get('db'));
    }

    protected function afterCreate($service, $id) {
        if ($service instanceof IServiceManagerAware) {
            $service->setServiceManager($this);
        }
    }
}
