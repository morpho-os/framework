<?php
namespace Morpho\Code\Compiler;

class Parsing implements ICompilerPhase {
    private $parser;

    public function __construct($parser = null) {
        $this->parser = $parser;
    }
}