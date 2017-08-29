<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class NotPredicate extends ParsingExpression {
    private $expression;

    public function __construct($expression) {
        $this->expression = $expression;
    }

    public function parse($input, Peg $peg) {
        $res = $this->expression->parse($input, $peg);
        return false === $res ? '' : false;
    }
}