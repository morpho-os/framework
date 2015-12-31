<?php
namespace Morpho\Code\Compiler;

interface ICompilerPhase {
    public function run($input);
}