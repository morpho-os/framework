<?php declare(strict_types=1);
namespace Morpho\Code\Compiler\FrontEnd;

interface IFrontEndFactory {
    public function mkInitializer(): IInitializer;

    public function mkLexicalAnalyzer(): ILexicalAnalyzer;

    public function mkSyntaxAnalyzer(): ISyntaxAnalyzer;

    public function mkSemanticAnalyzer(): ISemanticAnalyzer;

    public function mkIntermediateCodeGen(): IIntermediateCodeGen;
}
