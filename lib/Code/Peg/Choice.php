<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class Choice extends ParsingExpression {
    private $expressions;

    public function __construct(...$variants) {
        $this->expressions = $variants;
    }

    public function parse($input, Peg $peg) {
        foreach ($this->expressions as $expression) {
            $match = $expression->parse($input, $peg);
            if (false !== $match) {
                return $match;
            }
            $input = substr($input, strlen($match));
        }
        return false;
    }
}