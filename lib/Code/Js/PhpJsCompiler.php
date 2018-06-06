<?php declare(strict_types=1);
namespace Morpho\Code\Js;
use Morpho\Code\Compiler\Compiler;

class PhpJsCompiler extends Compiler {
    public function __construct() {
        $config = [
            'frontEndPhases' => [
                new Parser(),
                new SemanticAnalyzer(),
            ],
            'middleEndPhases' => [
                new Optimizer(),
            ],
            'backEndPhases' => [
                new CodeGen(),
            ],
        ];
        parent::__construct($config);
    }
}
