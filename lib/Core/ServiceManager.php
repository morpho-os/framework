<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\MethodNotFoundException;
use Morpho\Di\ServiceManager as BaseServiceManager;
use function Morpho\Code\composerAutoloader;

abstract class ServiceManager extends BaseServiceManager {
    protected $config;

    public function __construct(array $services = null) {
        parent::__construct($services);
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

    protected function newAutoloaderService() {
        return composerAutoloader();
    }

    abstract protected function newModuleManagerService();

    protected function newModuleInstallerService() {
        $moduleInstaller = new ModuleInstaller();
        $moduleInstaller->setDb($this->get('db'));
        return $moduleInstaller;
    }

    protected function newSettingsManagerService() {
        return new SettingsManager($this->get('db'));
    }
}
