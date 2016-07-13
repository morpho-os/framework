<?php
namespace Morpho\Core;

use Morpho\Base\MethodNotFoundException;
use Morpho\Di\ServiceManager as BaseServiceManager;
use function Morpho\Code\composerAutoloader;

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

    protected function createAutoloaderService() {
        return composerAutoloader(VENDOR_DIR_PATH . '/' . AUTOLOAD_FILE_NAME);
    }

    abstract protected function createModuleManagerService();

    protected function createViewService() {
        return $this->get('moduleManager')
            ->get($this->config['view']);
    }

    protected function createSettingManagerService() {
        return new SettingManager($this->get('db'));
    }
}
