<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class Alternation extends ParsingExpression {
    private $expressions;

    public function __construct(...$expressions) {
        $this->expressions = $expressions;
    }

    public function parse($input) {
        foreach ($this->expressions as $expression) {
            $match = $expression->parse($input);
            if (false !== $match) {
                return $match;
            }
            $input = substr($input, strlen($match));
        }
        return false;
    }
}