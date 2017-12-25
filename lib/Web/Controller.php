<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use function Morpho\Base\dasherize;
use Morpho\Base\IFn;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Session\Session;
use Morpho\Core\Node;
use Morpho\Web\View\Page;

class Controller extends Node implements IFn {
    /**
     * @var \Morpho\Ioc\IServiceManager
     */
    protected $serviceManager;
    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __invoke($request): void {
        /** @var Request $request */
        $this->request = $request;
        $action = $request->actionName();
        if (empty($action)) {
            throw new \LogicException("Empty action name");
        }
        $this->beforeEach();
        $page = null;
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $page = $this->$method();
        }
        $this->afterEach();
        if ($page instanceof Response) {
            $request->setResponse($page);
            return;
        }
        if (!$request->isDispatched() || $request->response()->isRedirect()) {
            return;
        }
        if (null === $page || is_array($page)) {
            $page = $this->newPage($page);
        }
        $request->params()['page'] = $page;
    }

    public function setRequest(Request $request): void {
        $this->request = $request;
    }

    public function request(): ?Request {
        return $this->request;
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

    protected function messenger(): Messenger {
        return $this->serviceManager->get('messenger');
    }

    protected function forward(string $action, string $controller = null, string $module = null, array $routingParams = null): void {
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
            $request->params()['routing'] = $routingParams;
        }

        $request->isDispatched(false);
    }

    protected function redirect($uri = null, int $httpStatusCode = null): Response {
        $request = $this->request;
        if (null === $uri) {
            $uri = $request->uri();
        }
        $uri = prependBasePath(function () {
            return $this->request->uri()->path()->basePath();
        }, $uri);
        /** @var Response $response */
        $response = $request->response();
        return $response->redirect($uri, $httpStatusCode);
    }

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

    protected function newPage(array $vars = null): Page {
        return new Page(dasherize($this->request->actionName()), $vars);
    }
}