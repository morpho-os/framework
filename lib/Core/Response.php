<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class Response extends Message implements IResponse {
    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var int
     */
    protected $statusCode = 0;

    public function setBody(string $body): void {
        $this->body = $body;
    }

    public function body(): string {
        return $this->body;
    }

    public function isBodyEmpty(): bool {
        return !isset($this->body[0]);
    }

    public function send(): void {
        $this->sendBody();
    }

    public function setStatusCode(int $statusCode): void {
        $this->statusCode = $statusCode;
    }

    public function statusCode(): int {
        return $this->statusCode;
    }

    protected function sendBody(): void {
        echo $this->body;
    }
}