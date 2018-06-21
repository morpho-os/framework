<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

abstract class Request extends Message implements IRequest {
    /**
     * @var ?string
     */
    protected $moduleName;

    /**
     * @var ?string
     */
    protected $controllerName;

    /**
     * @var ?string
     */
    protected $actionName;

    /**
     * @var bool|null
     */
    private $isHandled = false;

    /**
     * @var ?IResponse
     */
    private $response;

    public function isHandled(bool $flag = null): bool {
        if ($flag !== null) {
            $this->isHandled = $flag;
        }
        return $this->isHandled;
    }

    public function setHandler(array $handler): void {
        $this->setModuleName($handler[0]);
        $this->setControllerName($handler[1]);
        $this->setActionName($handler[2]);
    }

    public function handler(): array {
        return [$this->moduleName(), $this->controllerName(), $this->actionName()];
    }

    public function setModuleName(string $moduleName): void {
        $this->moduleName = $moduleName;
    }

    public function moduleName(): ?string {
        return $this->moduleName;
    }

    public function setControllerName(string $controllerName): void {
        $this->controllerName = $controllerName;
    }

    public function controllerName(): ?string {
        return $this->controllerName;
    }

    public function setActionName(string $actionName): void {
        $this->actionName = $actionName;
    }

    public function actionName(): ?string {
        return $this->actionName;
    }

    public function setResponse(IResponse $response): void {
        $this->response = $response;
    }

    public function response(): IResponse {
        if (null === $this->response) {
            $this->response = $this->mkResponse();
        }
        return $this->response;
    }

    abstract protected function mkResponse(): IResponse;
}
