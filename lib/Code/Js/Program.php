<?php
declare(strict_types=1);

namespace Morpho\Code\Js;

class Program {
    private $compiler;
//    private $outFilePath;
    private $input = [];

    public function __construct(Compiler $compiler) {
        $this->compiler = $compiler;
    }

    public function append($input): self {
        $this->input[] = $input;
        return $this;
    }

    public function run(): CompilationResult {
        return $this->compiler->compile($this);
    }

    public function input(): iterable {
        return $this->input;
    }
}