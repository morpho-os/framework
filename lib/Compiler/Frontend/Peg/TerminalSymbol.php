<?php

declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Frontend\Peg;

class TerminalSymbol extends ParsingExpression {
    public $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function parse($input, Peg $peg) {
        if (0 === strpos($input, $this->value)) {
            return substr($input, 0, strlen($this->value));
        }
        return false;
    }
}