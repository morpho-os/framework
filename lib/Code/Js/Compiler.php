<?php
//declare(strict_types=1);
namespace Morpho\Code\Js;

abstract class Compiler {
    public function newCompilation(): Program {
        return new Program($this);
    }

    abstract public function compile(Program $program): CompilationResult;
}