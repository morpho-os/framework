<?php
declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Base\ArrayObject;
use function Morpho\Code\parseFile;
use PhpParser\PrettyPrinterAbstract;

// @TODO: Unify with the TypeScriptCompiler.
class PJsCompiler {
    public function compile(): CompilationUnit {
        return new CompilationUnit($this);
    }

    public function compileUnit(CompilationUnit $compUnit): void {
        foreach ($compUnit->inFilePaths() as $filePath) {
            $compUnit->appendResult($this->compileFile($filePath));
        }
    }

    public function compileFile(string $filePath): CompilationResult {
        $res = new CompilationResult();
        $res->filePath = $filePath;

        $ast = parseFile($filePath);

        //$ast = $this->rewriteTree($ast);

        $res->output = (new Generator())->__invoke($ast, $res);
        return $res;
    }
}

class Generator extends PrettyPrinterAbstract {
    public function __invoke(array $ast, CompilationResult $compRes): string {

        // apply "this go to that" functions



        return 'abc';
    }
}

class CompilationUnit {
    private $compiler;
    private $outFilePath;
    private $inFilePaths;
    private $result = [];

    public function __construct(PJsCompiler $compiler) {
        $this->compiler = $compiler;
        $this->result = new CompilationResult();
    }

    public function inFilePath(string $filePath): self {
        $this->inFilePaths[] = $filePath;
        return $this;
    }

    public function outFilePath(string $filePath): self {
        $this->outFilePath = $filePath;
        return $this;
    }

    public function run(): CompilationResult {
        $this->compiler->compileUnit($this);
        return $this->result;
    }

    public function inFilePaths(): iterable {
        return $this->inFilePaths;
    }

    public function appendResult(CompilationResult $result) {
        $this->result[] = $result;
    }
}

class CompilationResult extends ArrayObject {
}