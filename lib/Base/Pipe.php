<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class Pipe extends \ArrayObject implements IFn {
    public function __invoke($value) {
        foreach ($this as $fn) {
            $value = $fn($value);
        }
        return $value;
    }

    public function append($value): self {
        parent::append($value);
        return $this;
    }
}