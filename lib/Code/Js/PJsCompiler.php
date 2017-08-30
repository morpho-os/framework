<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Code\Js;

use function Morpho\Code\parse;
use PhpParser\NodeTraverser;

class PJsCompiler extends Compiler {
    public function __invoke($input): CompilationResult {
        $result = new CompilationResult();
        $result->append($this->compile_($input));
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

