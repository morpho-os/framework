<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

use Morpho\Base\NotImplementedException;
use Morpho\Code\SyntaxError;

class Peg /*implements \IteratorAggregate */{
    private $rules;

    public function __construct(array $rules) {
        $this->rules = $rules;
    }

    /**
     * Generates PHP code with Peg by the grammar.
     */
    public static function generate(string $grammar): string {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * Translates the grammar string into a Peg object.
     */
    public static function fromString(string $grammar): Peg {
        // @TODO: Parse the $grammar
        throw new NotImplementedException(__METHOD__);
/*        return new static([
            new Rule(new NonTerminalSymbol(), new TerminalSymbol($grammar))
        ]);*/
    }

    public function parse(string $input): string {
        //$offset = 0;
        $expression = reset($this->rules);
        $match = $expression->parse($input, $this);
        //foreach ($this->rules as $nonterminal => $expression) {
//            $match = $expression->parse($input, $this);
            if (false !== $match) {
                return $match;
/*                $n = strlen($match);
                $input = substr($input, $n);*/
                //$offset += $n;
            } else {
                throw new SyntaxError();
            }
    }

    public function rule(string $nonterminal): array {
        return [$nonterminal, $this->rules[$nonterminal]];
    }
}
/*
class Range {
    /**
     * @var Position
     * /
    public $start;
    /**
     * @var Position
     * /
    public $end;
}
class Position {
    /**
     * @var int [0..inf)
     * /
    public $offset;
    /**
     * @var int [1..inf)
     * /
    public $lineNo;
    /**
     * @var int [1..inf)
     * /
    public $columnNo;
}*/