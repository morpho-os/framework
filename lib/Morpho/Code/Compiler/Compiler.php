<?php
namespace Morpho\Code\Compiler;

class Compiler {
    public function run(string $input) {
        $phases = [
            new LexicalAnalysis(),
            new Parsing(),
            new SemanticAnalysis(),
            new Optimization(),
            new CodeGeneration(),
        ];
        foreach ($phases as $phase) {
            $input = $phase->run($input);
        }
        return $input;
    }
}