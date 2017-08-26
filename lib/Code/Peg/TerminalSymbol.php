<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class TerminalSymbol extends ParsingExpression {
    private $value;

    public function __construct(string $value) {
        $this->value = $value;
    }
}