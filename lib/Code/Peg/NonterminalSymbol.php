<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class NonterminalSymbol extends ParsingExpression {
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function parse($input, Peg $peg) {
        $expression = $peg->rule($this->value)[1];
        return $expression->parse($input, $peg);
    }
}