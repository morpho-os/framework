<?php
declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Core\Controller as BaseController;
use Zend\Json\Json;

class Controller extends BaseController {
    private $viewVars = [];

    /**
     * @param string $name
     * @return string
    public function renderWidget($name)
     * {
     * return $this->renderView($this->getViewPath($name) . '-widget');
     * }
     */

    /**
     * @param $request
     * @throws \LogicException
     */
    public function dispatch($request) {
        $this->viewVars = [];

        $this->request = $request;

        $action = $request->getActionName();

        if (empty($action)) {
            throw new \LogicException();
        }

        $this->beforeEach();

        $viewVars = [];
        $method = $action . 'Action';
        if (method_exists($this, $method)) {
            $viewVars = $this->$method();
            if (null === $viewVars) {
                $viewVars = [];
            }
        }

        $this->afterEach();

        if (is_string($viewVars)) {
            $this->request->getResponse()
                ->setContent($viewVars);
        } elseif ($this->shouldRenderView()) {
            $this->request->getResponse()
                ->setContent(
                    $this->renderView(
                        isset($this->viewVars['name']) ? $this->viewVars['name'] : $action,
                        $viewVars
                    )
                );
        }
    }

    protected function beforeEach() {
        //$this->autoDecodeRequestJson();
        /*
        $request = $this->request;
        $header = $request->getHeader('Content-Type');
        if (false !== $header && false !== stripos($header->getFieldValue(), 'application/json')) {
            $data = Json::decode($request->getContent());
            $request->replace((array) $data);
        }
        */
    }

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

    // @codingStandardsIgnoreStart
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

    // @codingStandardsIgnoreEnd

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

    /**
     * @return string
     */
    protected function messagesAsJson() {
        return $this->asJson($this->getMessages());
    }

    /**
     * @param mixed
     * @return string
     */
    protected function asJson($data) {
        $this->request->getResponse()
            ->getHeaders()
            ->addHeaderLine('Content-Type', 'application/json');
        return Json::encode($data);
    }

    protected function asJsonOrHtml($data) {
        return $this->request->isAjax()
            ? $this->asJson($data)
            : $data;
    }

    protected function success($data = null) {
        return $this->asJsonOrHtml(array_merge((array)$data, ['success' => true]));
    }

    protected function error($data = null) {
        return $this->asJsonOrHtml(array_merge((array)$data, ['error' => true]));
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
        $this->viewVars['layout'] = $name;
    }

    protected function setView($name) {
        $this->viewVars['name'] = $name;
    }

    protected function setViewVars(array $vars) {
        $this->viewVars['instanceVars'] = $vars;
    }

    /**
     * @return bool
     */
    protected function shouldRenderView() {
        return $this->request->isDispatched();
    }

    /**
     * @param $viewName
     * @param array $viewVars
     * @return string
     */
    protected function renderView($viewName, array $viewVars = []) {
        $viewVars['node'] = $this;
        return $this->trigger(
            'render',
            array_merge(
                [
                    'name' => $viewName,
                    'vars' => $viewVars
                ],
                $this->viewVars
            )
        );
    }

    protected function getUserManager() {
        return $this->serviceManager->get('userManager');
    }
}
