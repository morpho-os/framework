<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * [Monad](https://github.com/fantasyland/static-land/blob/master/docs/spec.md#monad)
 * It does not have return()/unit()/op() method as for OOP, unit is the same as calling constructor.
 */
interface IMonad extends IApplicative, IChain {
}
