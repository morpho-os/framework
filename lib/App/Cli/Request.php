<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\IResponse;
use Morpho\App\Request as BaseRequest;
use Morpho\Base\NotImplementedException;

class Request extends BaseRequest {
    /**
     * @var array
     */
    protected $args;

    public function setArgs(array $args): void {
        $this->args = $args;
    }

    public function args($namesOrIndexes = null) {
        if (null === $this->args) {
            $this->args = $_SERVER['argv'];
        }
        return $this->args;
    }

    /**
     * @param string|int $nameOrIndex
     * @return mixed
     */
    public function arg($nameOrIndex) {
        throw new NotImplementedException();
    }

    protected function mkResponse(): IResponse {
        return new Response();
    }
}
