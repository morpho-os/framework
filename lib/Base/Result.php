<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use JsonSerializable;
use UnexpectedValueException;

/**
 * Useful for monadic error-handling code which can be composed. Inspired by F#, Haskell and Rust.
 */
abstract class Result extends Monad implements JsonSerializable {
    public function bind(callable $fn): Result {
        if ($this instanceof Err) {
            return $this;
        }
        if ($this instanceof Ok) {
            return $fn($this->val);
        }
        throw new UnexpectedValueException();
    }

    public function apply(IFunctor $functor): Result {
        return $functor->map(function ($fn) {
            return $fn($this->val);
        });
    }

    abstract public function isOk(): bool;
}
