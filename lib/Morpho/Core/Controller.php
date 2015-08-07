<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

abstract class Controller extends Node implements IServiceManagerAware {
    protected $serviceManager;

    protected $request;

    abstract public function dispatch($request);

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    protected function getAccessManager() {
        return $this->serviceManager->get('accessManager');
    }

    protected function trigger($event, array $args = null) {
        return $this->getParent('ModuleManager')->trigger($event, $args);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function nameToClass(string $name): string {
        return $name;
    }

    /**
     * Called before calling of any action.
     */
    protected function beforeEach() {
    }

    /**
     * Called after calling of any action.
     */
    protected function afterEach() {
    }

    protected function setSetting($name, $value, $moduleName = null) {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->getModuleName());
    }

    protected function getSetting($name, $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->getModuleName());
    }

    /**
     * @return string
     */
    protected function getModuleName() {
        return $this->parent->getName();
    }

    protected function getDb() {
        return $this->serviceManager->get('db');
    }

    protected function getRepo($name) {
        return $this->parent->getRepo($name);
    }
}