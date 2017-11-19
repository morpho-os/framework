<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

use Morpho\Core\IResponse;
use Morpho\Core\Request as BaseRequest;

class Request extends BaseRequest {
    /**
     * @var array
     */
    protected $args;

    public function setArgs(array $args) {
        $this->args = $args;
    }

    public function args(): array {
        if (null === $this->args) {
            $this->args = $_SERVER['argv'];
        }
        return $this->args;
    }

    protected function newResponse(): IResponse {
        return new Response();
    }
}