<?php
declare(strict_types=1);
namespace Morpho\Code\Js;

use function Morpho\Code\parse;
use PhpParser\NodeTraverser;

class PJsCompiler extends Compiler {
    public function compile(Program $program): CompilationResult {
        $result = new CompilationResult();
        foreach ($program->input() as $file) {
            $result->append($this->compile_($file));
        }
        return $result;
    }

    private function compile_($input): CompilationResult {
        $res = new CompilationResult();
        //$res->filePath = $filePath;

        //$ast = parseFile($filePath);

        $ast = parse($input);

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

