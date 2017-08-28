<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class NotPredicate extends ParsingExpression {
    private $expression;

    public function __construct($expression) {
        $this->expression = $expression;
    }

    public function parse($input) {
        $res = $this->expression->parse($input);
        return false === $res ? '' : false;
    }
}