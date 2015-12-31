<?php
namespace Morpho\Code\Compiler;

/**
 * Based on lections by Alex Aiken
 */
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