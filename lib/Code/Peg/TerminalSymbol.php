<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class TerminalSymbol extends ParsingExpression {
    public $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function parse($input) {
        if (0 === strpos($input, $this->value)) {
            return substr($input, 0, strlen($this->value));
        }
        return false;
    }
}