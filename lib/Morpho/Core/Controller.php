<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

abstract class Controller extends Node implements IServiceManagerAware {
    protected $serviceManager;

    protected $request;

    private $viewVars = [];
    private $specialViewVars = [];

    public function dispatch($request) {
        $this->viewVars = $this->specialViewVars = [];

        $this->request = $request;

        $action = $request->getActionName();

        if (empty($action)) {
            throw new \LogicException();
        }

        $this->beforeEach();

        $actionResult = [];
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $actionResult = $this->$method();
            if (null === $actionResult) {
                $actionResult = [];
            }
        }

        $this->afterEach();

        if (is_string($actionResult)) {
            $this->request->getResponse()
                ->setContent($actionResult);
        } elseif ($this->shouldRenderView()) {
            $this->request->getResponse()
                ->setContent(
                    $this->renderView(
                        isset($this->specialViewVars['name']) ? $this->specialViewVars['name'] : $action,
                        $actionResult // $actionResult is view vars.
                    )
                );
        }
    }

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

    protected function trigger(string $event, array $args = null) {
        return $this->getParent('ModuleManager')->trigger($event, $args);
    }

    protected function childNameToClass(string $name): string {
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

    protected function setSetting(string $name, $value, $moduleName = null) {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->getModuleName());
    }

    protected function getSetting(string $name, $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->getModuleName());
    }

    /**
     * @return string
     */
    protected function getModuleName() {
        return $this->parent->getName();
    }

    /**
     * @return \Morpho\Db\Db
     */
    protected function getDb() {
        return $this->serviceManager->get('db');
    }

    protected function getRepo(string $name) {
        return $this->parent->getRepo($name);
    }

    protected function setView(string $viewName) {
        $this->specialViewVars['name'] = $viewName;
    }

    protected function setSpecialViewVar(string $name, $value) {
        $this->specialViewVars[$name] = $value;
    }

    protected function setViewInstanceVars(array $vars) {
        $this->specialViewVars['instanceVars'] = array_merge(
            isset($this->specialViewVars['instanceVars'])
                ? $this->specialViewVars['instanceVars']
                : [],
            $vars
        );
    }

    protected function shouldRenderView(): bool {
        return $this->request->isDispatched();
    }

    /**
     * @return string
     */
    protected function renderView(string $viewName, array $viewVars = []) {
        return $this->trigger(
            'render',
            array_merge(
                $this->specialViewVars,
                [
                    'node' => $this,
                    'name' => $viewName,
                    'vars' => $viewVars
                ]
            )
        );
    }
}