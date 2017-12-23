<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Event;
use Morpho\Base\IFn;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Session\Session;
use Morpho\Web\View\View;
use Morpho\Core\Node;

class Controller extends Node implements IFn {
    /**
     * @var \Morpho\Di\IServiceManager
     */
    protected $serviceManager;
    /**
     * @var \Morpho\Web\Request
     */
    protected $request;
    /**
     * @var \Morpho\Web\View\View
     */
    private $view;

    public function __invoke($request): void {
        /** @var \Morpho\Core\Request $request */
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
                ->setBody($view);
        } else {
            // $view: null|array|View
            if (!$view instanceof View) {
                // $view: null|array
                $view = $this->view ?: $this->newView($view);
            }
            if ($this->shouldRenderView($view)) {
                $renderedView = $this->renderView($view);
                $this->request->response()
                    ->setBody($renderedView);
            }
        }
    }

    public function setRequest($request): void {
        $this->request = $request;
    }

    public function request() {
        return $this->request;
    }

    protected function trigger(Event $event) {
        return $this->serviceManager->get('eventManager')->trigger($event);
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

    protected function addSuccessMessage(string $message, array $args = null): void {
        $this->messenger()->addSuccessMessage($message, $args);
    }
    protected function addErrorMessage(string $message, array $args = null): void {
        $this->messenger()->addErrorMessage($message, $args);
    }
    protected function addWarningMessage(string $message, array $args = null): void {
        $this->messenger()->addWarningMessage($message, $args);
    }

    protected function messenger(): Messenger {
        return $this->serviceManager->get('messenger');
    }

    protected function shouldRenderView(View $view): bool {
        $request = $this->request;
        return $request->isDispatched()
            && !$request->response()->isRedirect()
            && !$view->isRendered();
    }

    /**
     * @param string|View $nameOrView
     */
    protected function setView($nameOrView): void {
        $this->view = is_string($nameOrView) ? new View($nameOrView) : $nameOrView;
    }

    protected function renderView(View $view): string {
        return $this->trigger(new Event('render', ['view' => $view]));
    }

    /**
     * @param \ArrayObject|array $routingParams
     */
    protected function forwardToAction(string $action, string $controller = null, string $module = null, $routingParams = null): void {
        $request = $this->request;

        if (null === $module) {
            $module = $this->parent()->name();
        }
        if (null === $controller) {
            $controller = $this->name();
        }

        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);

        if (null !== $routingParams) {
            $request->setRoutingParams($routingParams);
        }

        $request->isDispatched(false);
    }

    protected function redirect($uri = null, int $httpStatusCode = null): void {
        $request = $this->request;
        if (null === $uri) {
            $uri = $request->uri();
        }
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        /** @var Response $response */
        $response = $request->response();
        $response->redirect($uri, $httpStatusCode);
    }

/*    protected function ok($data = null): array {
        return ['ok' => $data ?: true];
    }

    protected function error($data = null): array {
        return ['error' => $data ?: true];
    }*/

    protected function accessDenied(): void {
        throw new AccessDeniedException();
    }

    protected function notFound(): void {
        throw new NotFoundException();
    }

    protected function badRequest(): void {
        throw new BadRequestException();
    }

    protected function session(string $key = null): Session {
        return new Session(get_class($this) . ($key ?: ''));
    }

    public function args($name = null, bool $trim = true) {
        return $this->request->args($name, $trim);
    }

    protected function data(array $source, $name = null, bool $trim = true) {
        return $this->request->data($source, $name, $trim);
    }

    protected function isPost(): bool {
        return $this->request->isPostMethod();
    }

    protected function post($name = null, bool $trim = true) {
        return $this->request->post($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    /**
     * @param string|View $nameOrLayout
     */
    protected function setLayout($nameOrLayout): void {
        $this->request->params()->offsetSet(
            'layout',
            is_string($nameOrLayout) ? new View($nameOrLayout) : $nameOrLayout
        );
    }

    protected function userManager() {
        return $this->serviceManager->get('userManager');
    }

    protected function newView(array $vars = null, array $properties = null, bool $isRendered = null): View {
        return new View($this->request->actionName(), $vars, $properties, $isRendered);
    }
}
