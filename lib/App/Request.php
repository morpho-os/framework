<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

abstract class Request extends Message implements IRequest {
    private array $handler = [];

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
        $this->handler = $handler;
    }

    public function handler(): array {
        return $this->handler;
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
