<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

abstract class Monoid extends Semigroup implements IMonoid {
    /**
     * (list: [T]) => T
     * @param iterable $list [T]
     * @return mixed T
     */
    public function mconcat(iterable $list): mixed {
        $val = $this->mempty();
        foreach ($list as $cur) {
            $val = $this->mappend($val, $cur);
        }
        return $val;
    }

    public abstract function mempty(): mixed;
}