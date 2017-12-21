<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * NB: IFn is callable, the following code returns true:
 *     is_callable(new class implements IFn { public function __invoke($value) {} });
 */
interface IFn {
    public function __invoke($value);
}