<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\IFn;

abstract class Controller extends Node implements IFn {
    /**
     * @var \Morpho\Di\IServiceManager
     */
    protected $serviceManager;

    protected $request;

    private $view;

    public function __invoke($request): void {
        $this->request = $request;
        $this->view = null;

        $action = $request->actionName();

        if (empty($action)) {
            throw new \LogicException("Empty action name");
        }

        $this->beforeEach();

        /** @var null|string|array|View $view */
        $view = null;
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $view = $this->$method();
        }

        $this->afterEach();

        if (is_string($view)) {
            // Already rendered View.
            $this->request->response()
                ->setContent($view);
        } else {
            // $view: null|array|View
            if (!$view instanceof View) {
                // $view: null|array
                $view = $this->view ?: new View($action, $view);
            }
            if ($this->shouldRenderView($view)) {
                $renderedView = $this->renderView($view);
                $this->request->response()
                    ->setContent($renderedView);
            }
        }
    }

    public function setRequest($request): void {
        $this->request = $request;
    }

    public function request() {
        return $this->request;
    }

    protected function trigger(string $event, array $args = null) {
        return $this->parent('ModuleManager')->trigger($event, $args);
    }

    /**
     * Called before calling of any action.
     */
    protected function beforeEach(): void {
    }

    /**
     * Called after calling of any action.
     */
    protected function afterEach(): void {
    }

    protected function setSetting(string $name, $value, string $moduleName = null): void {
        $this->serviceManager->get('settingsManager')
            ->set($name, $value, $moduleName ?: $this->moduleName());
    }

    /**
     * @return mixed
     */
    protected function setting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingsManager')
            ->get($name, $moduleName ?: $this->moduleName());
    }

    protected function moduleName(): string {
        return $this->parent->name();
    }

    protected function db() {
        return $this->serviceManager->get('db');
    }

    protected function repo(string $name) {
        return $this->parent->repo($name);
    }

    protected function shouldRenderView(View $view): bool {
        $request = $this->request;
        return $request->isDispatched()
            && !$request->response()->isRedirect()
            && !$view->isRendered();
    }

    /**
     * @param string|View $name
     */
    protected function setView($nameOrView): void {
        $this->view = is_string($nameOrView) ? new View($nameOrView) : $nameOrView;
    }

    protected function renderView(View $view): string {
        return $this->trigger('render', ['view' => $view]);
    }
}
