<?php
namespace Morpho\Base;

abstract class Pipe implements IFn {
    public function __invoke(...$args) {
        foreach ($this->fns() as $fn) {
            $args = $fn(...$args);
        }
        return $args;
    }

    abstract protected function fns(): iterable;
}
