<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

abstract class Monad extends Container implements IMonad {
    public function map(callable $fn): IFunctor {
        return $this->bind(
            function ($val) use ($fn) {
                return new static($fn($val));
            }
        );
    }
}
