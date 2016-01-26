<?php
namespace Morpho\Code\Compiler;

use Morpho\Base\IFn;

class Compiler implements IFn {
    public function __invoke(...$args) {
        $phases = [
            new LexicalAnalysis(),
            new Parsing(),
            new SemanticAnalysis(),
            new Optimization(),
            new CodeGeneration(),
        ];
        foreach ($phases as $phase) {
            $args = $phase(...$args);
        }
        return $args;
    }
}