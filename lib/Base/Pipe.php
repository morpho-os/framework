<?php
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