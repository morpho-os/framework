<?php
declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\Controller as BaseController;

class Controller extends BaseController {
    protected function forwardToAction($action, $controller = null, $module = null, array $params = null) {
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
            $request->clearParams();
            foreach ($params as $name => $value) {
                $request->setParam($name, $value);
            }
        }

        $request->isDispatched(false);
    }

    protected function redirectToAction($action, $httpMethod = null, $controller = null, $module = null, array $params = null, array $args = null, array $options = null) {
        if (null === $controller) {
            $controller = $this->request->getControllerName();
        }
        if (null === $module) {
            $module = $this->request->getModuleName();
        }
        if (null === $httpMethod) {
            $httpMethod = Request::GET_METHOD;
        }
        $uri = $this->serviceManager
            ->get('router')
            ->assemble($action, $httpMethod, $controller, $module, $params);

        $this->redirectToUri($uri, $args, $options);
    }

    protected function redirectToUri($uri = null, array $params = null, array $args = null, array $options = null, $httpStatusCode = null) {
        $response = $this->request->getResponse();
        $response->redirect(
            $this->request->getRelativeUri($uri, $params, $args, $options),
            true,
            $httpStatusCode
        );
    }

    protected function redirectToSelf($successMessage = null) {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        $this->redirectToUri($this->request->getUri()->getPath());
    }

    protected function redirectToHome($successMessage = null) {
        if (null !== $successMessage) {
            $this->addSuccessMessage($successMessage);
        }
        return $this->redirectToUri('/');
    }

    protected function success($data = null) {
        return $this->normalize($data, 'success');
    }

    protected function error($data = null) {
        return $this->normalize($data, 'error');
    }

    private function normalize($data, $key) {
        return is_scalar($data)
            ? [$key => (string) $data]
            : array_merge((array)$data, [$key => true]);
    }

    protected function getMessages(bool $clear = true): array {
        $messenger = $this->serviceManager->get('messenger');
        $messages = $messenger->toArray();
        if ($clear) {
            $messenger->clearMessages();
        }
        return $messages;
    }

    protected function addSuccessMessage($message, ...$args) {
        $this->serviceManager->get('messenger')->addSuccessMessage($message, ...$args);
    }

    protected function addErrorMessage($message, ...$args) {
        $this->serviceManager->get('messenger')->addErrorMessage($message, ...$args);
    }

    protected function addWarningMessage($message, ...$args) {
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

    public function getArg($name, $trim = true) {
        return $this->request->getArg($name, $trim);
    }

    public function getArgs($name = null, $trim = true) {
        return $this->request->getArgs($name, $trim);
    }

    protected function getPost($name = null, $trim = true) {
        return $this->request->getPost($name, $trim);
    }

    protected function getGet($name = null, $trim = true) {
        return $this->request->getGet($name, $trim);
    }

    protected function setLayout($name) {
        $this->setSpecialViewVar('layout', $name);
    }

    protected function getUserManager() {
        return $this->serviceManager->get('userManager');
    }
}
