<?php
namespace Morpho\Core;

abstract class Request {
    protected $response;

    protected $moduleName;

    protected $controllerName;

    protected $actionName;

    protected $params = [];

    private $isDispatched = false;

    /**
     * @param bool|null $flag
     * @return bool
     */
    public function isDispatched($flag = null) {
        if ($flag !== null) {
            $this->isDispatched = $flag;
        }
        return $this->isDispatched;
    }

    public function setHandler(array $handler): Request {
        return $this->setModuleName($handler[0])
            ->setControllerName($handler[1])
            ->setActionName($handler[2]);
    }

    public function getHandler(): array {
        return [$this->getModuleName(), $this->getControllerName(), $this->getActionName()];
    }

    public function setModuleName(string $moduleName) {
        $this->moduleName = $moduleName;
        return $this;
    }

    public function getModuleName() {
        return $this->moduleName;
    }

    public function setControllerName(string $controllerName) {
        $this->controllerName = $controllerName;
        return $this;
    }

    public function getControllerName() {
        return $this->controllerName;
    }

    public function setActionName(string $actionName) {
        $this->actionName = $actionName;
        return $this;
    }

    public function getActionName() {
        return $this->actionName;
    }

    public function hasParams(): bool {
        return count($this->params) > 0;
    }

    public function setParams(array $params) {
        $this->params = $params;
    }

    public function getParams() {
        return $this->params;
    }

    public function clearParams() {
        $this->params = [];
    }

    public function setParam($name, $value) {
        $this->params[$name] = $value;
    }

    public function getParam($name, $default = null) {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse() {
        if (null === $this->response) {
            $this->response = $this->createResponse();
        }
        return $this->response;
    }

    protected abstract function createResponse();
} 