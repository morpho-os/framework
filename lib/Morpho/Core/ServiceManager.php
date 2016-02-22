<?php
namespace Morpho\Core;

use Morpho\Base\MethodNotFoundException;
use Morpho\Di\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected $config;

    public function __construct(array $config = null, array $services = null) {
        parent::__construct($services);
        $this->config = $config;
        $this->setAliases(['dispatcher' => 'modulemanager']);
    }

    /**
     * Replaces the calls in form get$name() with the get($name), for example: getFoo() -> get('foo').
     */
    public function __call($method, array $args) {
        if (substr($method, 0, 3) === 'get' && strlen($method) > 3) {
            return $this->get(substr($method, 3));
        }
        throw new MethodNotFoundException($this, $method);
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
