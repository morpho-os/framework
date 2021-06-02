<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * Right identity
 *     x <> mempty = x
 * Left identity
 *     mempty <> x = x
 * Associativity
 *     x <> (y <> z) = (x <> y) <> z (Semigroup law)
 * Concatenation
 *     mconcat = foldr (<>) mempty
 */
interface IMonoid extends ISemigroup {
    public function mempty(): mixed;

    public function mconcat(iterable $list): mixed;
}