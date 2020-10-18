<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * [Functor](https://github.com/fantasyland/static-land/blob/master/docs/spec.md#functor)
 */
interface IFunctor extends IContainer {
    /**
     * @param callable $fn: A => B
     *     Function can return any value which must be wrapped in IFunctor container.
     */
    public function map(callable $fn): IFunctor;
}
