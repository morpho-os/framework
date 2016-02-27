<?php
namespace Morpho\Code\Compiler;

use Morpho\Base\IFn;

class Compiler implements IFn {
    public function __invoke(...$args) {
        foreach ($this->phases() as $phase) {
            $args = $phase(...$args);
        }
        return $args;
    }

    protected function phases() {
        return [
            new LexicalAnalysis(),
            new Parsing(),
            new SemanticAnalysis(),
            new Optimization(),
            new CodeGeneration(),
        ];
    }
}