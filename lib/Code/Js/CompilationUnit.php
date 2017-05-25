<?php
declare(strict_types=1);

namespace Morpho\Code\Js;

class CompilationUnit {
    private $compiler;
//    private $outFilePath;
    private $input = [];
    private $result = [];

    public function __construct(PJsCompiler $compiler) {
        $this->compiler = $compiler;
        $this->result = new CompilationResult();
    }

    public function add($input): self {
        $this->input[] = $input;
        return $this;
    }

    /*    public function outFilePath(string $filePath): self {
            $this->outFilePath = $filePath;
            return $this;
        }*/

    public function run(): CompilationResult {
        $this->compiler->compileFile($this);
        return $this->result;
    }

    public function sourceFiles(): iterable {
        return $this->input;
    }

    public function appendResult(CompilationResult $result) {
        $this->result[] = $result;
    }
}