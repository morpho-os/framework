<?php
namespace Morpho\Base;

abstract class Pipe implements IFn {
    public function __invoke($value) {
        foreach ($this->fns() as $fn) {
            $value = $fn($value);
        }
        return $value;
    }

    abstract protected function fns(): iterable;
}
