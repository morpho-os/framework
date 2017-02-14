<?php
namespace Morpho\Code\Compiler;

use Morpho\Base\Pipe;

abstract class Compiler extends Pipe {
    protected function fns(): iterable {
        return [
            $this->frontend(),
            $this->optimization(),
            $this->backend(),
        ];
    }

    abstract protected function frontend(): ICompilerPhase;

    abstract protected function optimization(): ICompilerPhase;

    abstract protected function backend(): ICompilerPhase;
}