<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Core\Controller as BaseController;
use Morpho\Web\Session\Session;

class Controller extends BaseController {
    protected function forwardToAction(string $action, string $controller = null, string $module = null, array $routingParams = null): void {
        $request = $this->request;

        if (null === $module) {
            $module = $this->parent()->name();
        }
        if (null === $controller) {
            $controller = $this->name();
        }

        $request->setModuleName($module)
            ->setControllerName($controller)
            ->setActionName($action);

        if (null !== $routingParams) {
            $request->setRoutingParams($routingParams);
        }

        $request->isDispatched(false);
    }

    protected function redirectToAction(string $action, string $controller = null, string $module = null, string $httpMethod = null, array $routingParams = null): void {
        // @TODO
        throw new NotImplementedException(__METHOD__);
        /*
        if (null === $controller) {
            $controller = $this->request->controllerName();
        }
        if (null === $module) {
            $module = $this->request->moduleName();
        }
        if (null === $httpMethod) {
            $httpMethod = Request::GET_METHOD;
        }
        return $this->redirectToUri(
            $this->serviceManager
                ->get('router')
                ->assemble($action, $httpMethod, $controller, $module, $params)
        );
        */
    }

    protected function redirectToUri(string $uri = null, int $httpStatusCode = null): void {
        $request = $this->request;
        if ($request->hasQuery('redirect')) {
            $uri = (new Uri($request->query('redirect')))->unsetQueryArg('redirect')->__toString();
        }
        $response = $request->response();
        $response->redirect($request->uri()->prependWithBasePath($uri), $httpStatusCode);
    }

    protected function redirectToSelf(string $successMessage = null, $queryArgs = null, string $fragment = null): void {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        $uri = $this->request->uri();
        if ($queryArgs) {
            $uri->setQuery($queryArgs);
        }
        if ($fragment) {
            $uri->setFragment($fragment);
        }
        $this->redirectToUri($uri->__toString());
    }

    protected function redirectToHome(string $successMessage = null): void {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        $this->redirectToUri('/');
    }

    protected function success($data = null): array {
        //if (!$this->request->isAjax()) {
        return ['success' => $data ?: true];
        /*}
        $this->addSuccessMessage(...$data);
        return null;
        */
    }

    protected function error($data = null): array {
        return ['error' => $data ?: true];
        /*
        if (!$this->request->isAjax()) {
        }
        $this->addErrorMessage(...$data);
        */
    }

    protected function messages(bool $clear = true): array {
        $messenger = $this->serviceManager->get('messenger');
        $messages = $messenger->toArray();
        if ($clear) {
            $messenger->clearMessages();
        }
        return $messages;
    }

    protected function addSuccessMessage(string $message, array $args = null): void {
        $this->serviceManager->get('messenger')->addSuccessMessage($message, $args);
    }

    protected function addErrorMessage(string $message, array $args = null): void {
        $this->serviceManager->get('messenger')->addErrorMessage($message, $args);
    }

    protected function addWarningMessage(string $message, array $args = null): void {
        $this->serviceManager->get('messenger')->addWarningMessage($message, $args);
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

    protected function isPostMethod(): bool {
        return $this->request->isPostMethod();
    }

    protected function post($name = null, bool $trim = true) {
        return $this->request->post($name, $trim);
    }

    protected function query($name = null, bool $trim = true) {
        return $this->request->query($name, $trim);
    }

    protected function setLayout(string $name): void {
        $this->setSpecialViewVar('layout', $name);
    }

    protected function userManager() {
        return $this->serviceManager->get('userManager');
    }
}
