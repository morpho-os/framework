<?php
declare(strict_types=1);
namespace Morpho\Code\Js;

use function Morpho\Code\parse;
use PhpParser\NodeTraverser;

// @TODO: Unify with the TypeScriptCompiler.
class PJsCompiler {
    public function newCompilation(): CompilationUnit {
        return new CompilationUnit($this);
    }

    public function compileFile($compUnit): void {
        foreach ($compUnit->sourceFiles() as $sourceFile) {
            $compUnit->appendResult($this->compileFile_($sourceFile));
        }
    }

    private function compileFile_($sourceFile): CompilationResult {
        $res = new CompilationResult();
        //$res->filePath = $filePath;

        //$ast = parseFile($filePath);

        $ast = parse($sourceFile);

        $ast = $this->rewrite($ast);

        $res->output = (new CodeGenerator())->prettyPrintFile($ast);
        return $res;
    }

    private function rewrite(array $ast): array {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new TreeRewriter());
        $ast = $traverser->traverse($ast);
        return $ast;
    }
}

