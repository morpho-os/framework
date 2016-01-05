<?php
namespace Morpho\Code\Compiler;

class Parsing implements ICompilerPhase {
    public function __construct($parser) {
        $this->parser = $parser;
    }

    public function run($input) {
        // TODO: Implement run() method.
    }
}