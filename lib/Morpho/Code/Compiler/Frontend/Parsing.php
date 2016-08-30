<?php
namespace Morpho\Code\Compiler\Frontend;

use Morpho\Code\Compiler\ICompilerPass;

class Parsing implements ICompilerPass {
    private $parser;

    public function __construct($parser = null) {
        $this->parser = $parser;
    }
}