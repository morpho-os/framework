<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\IResponse;
use Morpho\App\Request as BaseRequest;

class Request extends BaseRequest {
    protected ?array $args = null;

    public function setArgs(array $args): void {
        $this->args = $args;
    }

    public function args(string|array|null $namesOrIndexes = null): mixed {
        if (null === $this->args) {
            $this->args = $_SERVER['argv'];
        }
        return $this->args;
    }

    protected function mkResponse(): IResponse {
        return new Response();
    }
}
