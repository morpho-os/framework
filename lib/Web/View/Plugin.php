<?php
namespace Morpho\Web\View;

use Morpho\Base\IFn;

abstract class Plugin implements IFn {
    public function __invoke($value) {
        return $this;
    }
}