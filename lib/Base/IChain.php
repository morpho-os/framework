<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * [Chain](https://github.com/fantasyland/static-land/blob/master/docs/spec.md#chain)
 */
interface IChain extends IApply {
    /**
     * Applies $fn to the Container's internal value and wraps the result back into Container.
     * Aka bind().
     * @param callable $fn: A => IChain<B>
     * @return mixed IChain<B>
     */
    public function chain(callable $fn): IChain;
}
