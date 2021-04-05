<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Peg;

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