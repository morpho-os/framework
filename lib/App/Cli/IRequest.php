<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\IRequest as IBaseRequest;

interface IRequest extends IBaseRequest {
    public function setResponse(IResponse $response): void;

    public function response(): IResponse;

    public function args(string|array|null $namesOrIndexes = null): mixed;
}