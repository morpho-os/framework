<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

abstract class Controller extends Node implements IServiceManagerAware {
    protected $serviceManager;

    protected $request;

    private $viewVars = [];

    private $specialViewVars = [];

    public function dispatch($request)/*: void */ {
        $this->viewVars = $this->specialViewVars = [];

        $this->request = $request;

        $action = $request->getActionName();

        if (empty($action)) {
            throw new \LogicException("Empty action name");
        }

        $this->beforeEach();

        $actionResult = null;
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $actionResult = $this->$method();
        }

        $this->afterEach();

        if (is_string($actionResult)) {
            // Already rendered View.
            $this->request->getResponse()
                ->setContent($actionResult);
        } elseif ($this->shouldRenderView($actionResult)) {
            $renderedView = $this->renderView(
                isset($this->specialViewVars['name']) ? $this->specialViewVars['name'] : $action,
                $actionResult
            );
            $this->request->getResponse()
                ->setContent($renderedView);
        }
    }

    public function setServiceManager(IServiceManager $serviceManager)/*: void */ {
        $this->serviceManager = $serviceManager;
    }

    public function setRequest($request)/*: void */ {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    protected function trigger(string $event, array $args = null) {
        return $this->getParent('ModuleManager')->trigger($event, $args);
    }

    /**
     * Called before calling of any action.
     */
    protected function beforeEach()/*: void */ {
    }

    /**
     * Called after calling of any action.
     */
    protected function afterEach()/*: void */ {
    }

    protected function setSetting(string $name, $value, string $moduleName = null)/*: void */ {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->getModuleName());
    }

    protected function getSetting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->getModuleName());
    }

    protected function getModuleName(): string {
        return $this->parent->getName();
    }

    protected function db() {
        return $this->serviceManager->get('db');
    }

    protected function getRepo(string $name) {
        return $this->parent->getRepo($name);
    }

    protected function setView(string $viewName)/*: void */ {
        $this->specialViewVars['name'] = $viewName;
    }

    protected function setSpecialViewVar(string $name, $value)/*: void */ {
        $this->specialViewVars[$name] = $value;
    }

    protected function setViewInstanceVars(array $vars)/*: void */ {
        $this->specialViewVars['instanceVars'] = array_merge(
            isset($this->specialViewVars['instanceVars'])
                ? $this->specialViewVars['instanceVars']
                : [],
            $vars
        );
    }

    protected function shouldRenderView($actionResult): bool {
        return (is_array($actionResult) || !$this->request->getResponse()->isRedirect())
            && $this->request->isDispatched();
    }

    protected function renderView(string $viewName, array $viewVars = null): string {
        return $this->trigger(
            'render',
            array_merge(
                $this->specialViewVars,
                [
                    'controller' => $this->getName(),
                    'view' => $viewName,
                    'vars' => (array) $viewVars,
                ]
            )
        );
    }
}