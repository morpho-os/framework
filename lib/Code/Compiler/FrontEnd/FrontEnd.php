<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\FrontEnd;

use Morpho\Code\Compiler\CompilerPhase;

class FrontEnd extends CompilerPhase {
    public function diagnosticErrorMessages(): iterable {
        return [];
    }

    public function getIterator() {
        yield from [
            $this->mkInitializer(),
            $this->mkLexicalAnalyzer(),
            $this->mkSyntaxAnalyzer(),
            $this->mkSemanticAnalyzer(),
            $this->mkIntermediateCodeGen(),
        ];
    }

    protected function mkLexicalAnalyzer(): ILexicalAnalyzer {
        return new class implements ILexicalAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    protected function mkSyntaxAnalyzer(): ISyntaxAnalyzer {
        return new class implements ISyntaxAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    protected function mkSemanticAnalyzer(): ISemanticAnalyzer {
        return new class implements ISemanticAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    protected function mkIntermediateCodeGen(): IIntermediateCodeGen {
        return new class implements IIntermediateCodeGen {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    private function mkInitializer(): IInitializer {
        return new class implements IInitializer {
            public function __invoke($context) {
                return $context;
            }
        };
    }
}
