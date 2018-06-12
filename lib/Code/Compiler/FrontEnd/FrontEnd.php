<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\FrontEnd;

use Morpho\Code\Compiler\CompilerPhase;

abstract class FrontEnd extends CompilerPhase {
    abstract public function diagnosticErrorMessages(): iterable;

    public function getIterator() {
        return [
            $this->mkLexicalAnalyzer(),
            $this->mkSyntaxAnalyzer(),
            $this->mkSemanticAnalyzer(),
            $this->mkIntermediateCodeGen(),
        ];
    }

    abstract protected function mkLexicalAnalyzer(): ILexicalAnalyzer;

    abstract protected function mkSyntaxAnalyzer(): ISyntaxAnalyzer;

    abstract protected function mkSemanticAnalyzer(): ISemanticAnalyzer;

    abstract protected function mkIntermediateCodeGen(): IIntermediateCodeGen;
}
