<?php
namespace Morpho\Code\Compiler;

use Morpho\Base\Pipe;

class Compiler extends Pipe {
    protected function compose() {
        return [
            new Frontend(),
            new Optimization(),
            new Backend(),
        ];
    }
}