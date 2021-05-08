<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * IFunctor<A => B>
 */
interface IFunctor extends IContainer {
    /**
     * @param $fn : A => B
     *     The $fn can return any value which will be wrapped in IFunctor container by the map() implementation.
     */
    public function map(callable $fn): IFunctor;
}
