<?php
namespace Morpho\Web;

use Morpho\Core\Controller as BaseController;

class Controller extends BaseController {
    protected function forwardToAction(string $action, string $controller = null, string $module = null, array $params = null) {
        $request = $this->request;

        if (null === $module) {
            $module = $this->getParent()->getName();
        }
        if (null === $controller) {
            $controller = $this->getName();
        }

        $request->setModuleName($module)
            ->setControllerName($controller)
            ->setActionName($action);

        if (null !== $params) {
            $request->setParams($params);
        }

        $request->isDispatched(false);
    }

    protected function redirectToAction(string $action, string $httpMethod = null, string $controller = null, string $module = null, array $params = null) {
        if (null === $controller) {
            $controller = $this->request->getControllerName();
        }
        if (null === $module) {
            $module = $this->request->getModuleName();
        }
        if (null === $httpMethod) {
            $httpMethod = Request::GET_METHOD;
        }
        return $this->redirectToUri(
            $this->serviceManager
                ->get('router')
                ->assemble($action, $httpMethod, $controller, $module, $params)
        );
    }

    protected function redirectToUri(string $uri = null, int $httpStatusCode = null) {
        $request = $this->request;
        if ($request->hasGet('redirect')) {
            $uri = (new Uri($request->getGet('redirect')))->removeQueryArg('redirect')->__toString();
        }

        if ($request->isAjax()) {
            return $this->success(['redirect' => $uri]);
        }

        $response = $request->getResponse();
        $response->redirect(
            $request->currentUri()
                ->prependWithBasePath($uri),
            true,
            $httpStatusCode
        );
    }

    protected function redirectToSelf(string $successMessage = null, $queryArgs, string $fragment = null) {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        $uri = $this->request->currentUri();
        if ($queryArgs) {
            $uri->setQuery($queryArgs);
        }
        if ($fragment) {
            $uri->setFragment($fragment);
        }
        return $this->redirectToUri($uri->__toString());
    }

    protected function redirectToHome(string $successMessage = null) {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        return $this->redirectToUri('/');
    }

    protected function success($data = null) {
        return ['success' => $data ?: true];
    }

    protected function error($data = null) {
        return ['error' => $data ?: true];
    }

    protected function getMessages(bool $clear = true): array {
        $messenger = $this->serviceManager->get('messenger');
        $messages = $messenger->toArray();
        if ($clear) {
            $messenger->clearMessages();
        }
        return $messages;
    }

    protected function addSuccessMessage(string $message, ...$args) {
        $this->serviceManager->get('messenger')->addSuccessMessage($message, ...$args);
    }

    protected function addErrorMessage(string $message, ...$args) {
        $this->serviceManager->get('messenger')->addErrorMessage($message, ...$args);
    }

    protected function addWarningMessage(string $message, ...$args) {
        $this->serviceManager->get('messenger')->addWarningMessage($message, ...$args);
    }

    protected function accessDenied() {
        throw new AccessDeniedException();
    }

    protected function notFound() {
        throw new NotFoundException();
    }

    protected function getSession() {
        return $this->serviceManager->get('session');
    }

    protected function getParam($name) {
        return $this->request->getParam($name);
    }

    public function getArgs($name = null, bool $trim = true) {
        return $this->request->getArgs($name, $trim);
    }

    protected function data(array $source, $name, bool $trim = true) {
        return $this->request->data($source, $name, $trim);
    }

    protected function isPostMethod(): bool {
        return $this->request->isPostMethod();
    }

    protected function getPost($name = null, bool $trim = true) {
        return $this->request->getPost($name, $trim);
    }

    protected function getGet($name = null, bool $trim = true) {
        return $this->request->getGet($name, $trim);
    }

    protected function setLayout(string $name) {
        $this->setSpecialViewVar('layout', $name);
    }

    protected function getUserManager() {
        return $this->serviceManager->get('userManager');
    }
}
