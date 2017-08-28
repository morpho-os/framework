<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class Sequence extends ParsingExpression {
    private $expressions;

    public function __construct(array $expressions) {
        $this->expressions = $expressions;
    }

    public function parse($input) {
        $res = '';
        foreach ($this->expressions as $expression) {
            $match = $expression->parse($input);
            if (false === $match) {
                return false;
            }
            $res .= $match;
            $n = strlen($match);
            $input = substr($input, $n);
        }
        return $res;
    }
}