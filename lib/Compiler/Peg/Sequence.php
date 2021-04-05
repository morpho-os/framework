<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Peg;

class Sequence extends ParsingExpression {
    private $expressions;

    public function __construct(...$expressions) {
        $this->expressions = $expressions;
    }

    public function parse($input, Peg $peg) {
        $res = '';
        foreach ($this->expressions as $expression) {
            $match = $expression->parse($input, $peg);
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