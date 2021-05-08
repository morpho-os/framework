<?php

declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Frontend\Peg;

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