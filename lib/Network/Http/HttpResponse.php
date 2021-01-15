<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network\Http;

use Morpho\Base\NotImplementedException;
use Zend\Http\Response;

class HttpResponse {
    private Response $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    public function body(): string {
        return $this->response->getBody();
    }

    public function statusCode(): int {
        return $this->response->getStatusCode();
    }

    public function isOk(): bool {
        return $this->response->isOk();
    }

    public function header(string $name) {
        throw new NotImplementedException();
    }

    public function __toString(): string {
        return $this->response->getBody();
    }
}