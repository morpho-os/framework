<?php
namespace Morpho\Code\Compiler;

use Morpho\Base\Pipe;
use Morpho\Code\Compiler\Frontend\LexicalAnalysis;
use Morpho\Code\Compiler\Frontend\Parsing;

class Compiler extends Pipe {
    protected function compose() {
        return [
            new LexicalAnalysis(),
            new Parsing(),
            new SemanticAnalysis(),
            new Optimization(),
            new CodeGeneration(),
        ];
    }
}