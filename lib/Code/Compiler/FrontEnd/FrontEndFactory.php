<?php declare(strict_types=1);
namespace Morpho\Code\Compiler\FrontEnd;

class FrontEndFactory implements IFrontEndFactory {
    public function mkLexicalAnalyzer(): ILexicalAnalyzer {
        return new class implements ILexicalAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    public function mkSyntaxAnalyzer(): ISyntaxAnalyzer {
        return new class implements ISyntaxAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    public function mkSemanticAnalyzer(): ISemanticAnalyzer {
        return new class implements ISemanticAnalyzer {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    public function mkIntermediateCodeGen(): IIntermediateCodeGen {
        return new class implements IIntermediateCodeGen {
            public function __invoke($context) {
                return $context;
            }
        };
    }

    public function mkFrontEndPhases(): iterable {
        return [
            $this->mkLexicalAnalyzer(),
            $this->mkSyntaxAnalyzer(),
            $this->mkSemanticAnalyzer(),
            $this->mkIntermediateCodeGen(),
        ];
    }
}
