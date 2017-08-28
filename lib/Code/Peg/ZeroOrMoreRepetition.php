<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class ZeroOrMoreRepetition extends ParsingExpression {
    private $expression;

    public function __construct($expression) {
        $this->expression = $expression;
    }

    public function parse($input) {
        $res = '';
        while (true) {
            $match = $this->expression->parse($input);
            if (false === $match) {
                return $res;
            }
            $res .= $match;
            $input = substr($input, strlen($match));
        }
    }
}