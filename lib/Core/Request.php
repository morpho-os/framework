<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

abstract class Request {
    protected $moduleName;

    protected $controllerName;

    protected $actionName;

    protected $params;

    private $isDispatched = false;

    private $response;

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

    public function moduleName(): ?string {
        return $this->moduleName;
    }

    public function setControllerName(string $controllerName) {
        $this->controllerName = $controllerName;
        return $this;
    }

    public function controllerName(): ?string {
        return $this->controllerName;
    }

    public function setActionName(string $actionName) {
        $this->actionName = $actionName;
        return $this;
    }

    public function actionName(): ?string {
        return $this->actionName;
    }

    /**
     * Returns storage for internal params.
     */
    public function params(): \ArrayObject {
        if (null === $this->params) {
            $this->params = new \ArrayObject();
        }
        return $this->params;
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

    abstract protected function newResponse();
}