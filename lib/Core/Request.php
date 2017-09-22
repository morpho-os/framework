<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

abstract class Request {
    protected $response;

    protected $moduleName;

    protected $controllerName;

    protected $actionName;

    protected $routingParams = [];

    protected $internalParams = [];

    private $isDispatched = false;

    public function isDispatched(bool $flag = null): bool {
        if ($flag !== null) {
            $this->isDispatched = $flag;
        }
        return $this->isDispatched;
    }

    public function setHandler(array $handler): self {
        return $this->setModuleName($handler[0])
            ->setControllerName($handler[1])
            ->setActionName($handler[2]);
    }

    public function handler(): array {
        return [$this->moduleName(), $this->controllerName(), $this->actionName()];
    }

    public function setModuleName(string $moduleName) {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function moduleName() {
        return $this->moduleName;
    }

    public function setControllerName(string $controllerName) {
        $this->controllerName = $controllerName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function controllerName() {
        return $this->controllerName;
    }

    public function setActionName(string $actionName) {
        $this->actionName = $actionName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function actionName() {
        return $this->actionName;
    }

    public function hasRoutingParams(): bool {
        return count($this->routingParams) > 0;
    }

    public function setRoutingParams(array $params): void {
        $this->routingParams = $params;
    }

    public function routingParams(): array {
        return $this->routingParams;
    }

    public function setRoutingParam(string $name, $value): void {
        $this->routingParams[$name] = $value;
    }

    public function routingParam(string $name, $default = null) {
        return isset($this->routingParams[$name]) ? $this->routingParams[$name] : $default;
    }

    public function hasInternalParam(string $name): bool {
        return array_key_exists($name, $this->internalParams);
    }

    public function setInternalParam(string $name, $value): void {
        $this->internalParams[$name] = $value;
    }

    public function unsetInternalParam(string $name): void {
        unset($this->internalParams[$name]);
    }

    public function internalParam(string $name) {
        return $this->internalParams[$name];
    }

    public function internalParams(): array {
        return $this->internalParams;
    }

    public function setResponse($response): void {
        $this->response = $response;
    }

    public function response() {
        if (null === $this->response) {
            $this->response = $this->newResponse();
        }
        return $this->response;
    }

    protected abstract function newResponse();
} 