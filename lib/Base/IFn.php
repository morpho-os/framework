<?php
namespace Morpho\Base;

interface IFn {
    public function __invoke(...$args);
}