<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\Core\Dispatcher as BaseDispatcher;
use Morpho\App\Core\IRequest;

class Dispatcher extends BaseDispatcher {
    protected function throwNotFoundException(IRequest $request): void {
        throw new NotFoundException();
    }
}
