<?php
namespace Morpho\Base;

abstract class Pipe implements IFn {
    public function __invoke(...$args) {
        foreach ($this->compose() as $fn) {
            $args = $fn(...$args);
        }
        return $args;
    }

    abstract protected function compose();
}