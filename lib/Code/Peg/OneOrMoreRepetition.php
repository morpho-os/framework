<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class OneOrMoreRepetition extends ParsingExpression {
    private $expression;

    public function __construct($expression) {
        $this->expression = $expression;
    }

    public function parse($input, Peg $peg) {
        // TODO
    }
}