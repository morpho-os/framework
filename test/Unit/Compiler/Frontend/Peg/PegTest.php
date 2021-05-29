<?php

declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler\Frontend\Peg;

use Morpho\Compiler\Frontend\Peg\CharacterClass;
use Morpho\Compiler\Frontend\Peg\Choice;
use Morpho\Compiler\Frontend\Peg\EmptyString;
use Morpho\Compiler\Frontend\Peg\NonterminalSymbol;
use Morpho\Compiler\Frontend\Peg\NotPredicate;
use Morpho\Compiler\Frontend\Peg\OneOrMoreRepetition;
use Morpho\Compiler\Frontend\Peg\ParsingExpression;
use Morpho\Compiler\Frontend\Peg\Peg;
use Morpho\Compiler\Frontend\Peg\Sequence;
use Morpho\Compiler\Frontend\Peg\SyntaxError;
use Morpho\Compiler\Frontend\Peg\TerminalSymbol;
use Morpho\Compiler\Frontend\Peg\ZeroOrMoreRepetition;
use Morpho\Testing\TestCase;

class PegTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        $this->markTestIncomplete();
    }

    public function testInheritanceForExpressions() {
        /**
         * Base parsing expressions:
         *     ε       : empty string
         *     a       : terminal
         *     A       : nonterminal
         *     e1 e2   : sequence
         *     e1 / e2 : prioritized choice/alternation
         *     e*      : >= 0 repetitions
         *     !e      : not predicate
         * Extended parsing expressions (may be desugared to base parsing expressions)
         *      [a-z]  : character classes
         *      .      : any character
         *      ?      : option operator
         *      +      : one-or-more repetitions operator
         *      &      : and predicate operator
         * Other??
         *      ()     : grouping
         */
        $expressions = [
            // ε
            new EmptyString(),
            // a
            new TerminalSymbol('foo'),
            // A
            new NonterminalSymbol('F'),
            // e1 e2
            new Sequence(new TerminalSymbol('foo')),
            // e1 / e2
            new Choice(new TerminalSymbol('foo'), new TerminalSymbol('bar')),
            // e*
            new ZeroOrMoreRepetition(new TerminalSymbol('foo')),
            // !e
            new NotPredicate(new TerminalSymbol('foo')),
        ];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(ParsingExpression::class, $expression);
        }
    }

    public function testRule() {
        $e1 = new TerminalSymbol('foo');
        $e2 = new NonterminalSymbol('B');
        $peg = new Peg(['S' => $e1, 'X' => $e2]);
        $this->assertEquals(['S', $e1], $peg->rule('S'));
        $this->assertSame($e1, $peg->rule('S')[1]);
        $this->assertEquals(['X', $e2], $peg->rule('X'));
        $this->assertSame($e2, $peg->rule('X')[1]);
    }

    /**
     * Tests below correspond to chapter "3.3 Interpretation of a Grammar" from http://bford.info/pub/lang/peg.pdf
     */
    public function testParse_EmptyString() {
        $peg = new Peg(['S' => new EmptyString()]);
        $res = $peg->parse('');
        $this->assertEquals('', $res);
    }

    public function testParse_Terminal_SuccessCase() {
        $peg = new Peg(['S' => new TerminalSymbol('a')]);
        $res = $peg->parse('afoo');
        $this->assertEquals('a', $res);
    }

    public function dataParse_Terminal_FailureCase() {
        return [[''], ['bfoo']];
    }

    /**
     * @dataProvider dataParse_Terminal_FailureCase
     */
    public function testParse_Terminal_FailureCase(string $input) {
        $this->markTestIncomplete();
        $peg = new Peg(['S' => new TerminalSymbol('a')]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function testParse_Nonterminal() {
        $this->markTestIncomplete();
        /**
         * (A, x) => (n + 1, o) if A <- e in R and (e, x) => (n, o)
         */
        $peg = new Peg(['S' => new NonTerminalSymbol('N'), 'N' => new TerminalSymbol('foo')]);
        $res = $peg->parse('foo');
        $this->assertEquals('foo', $res);
    }

    public function testParse_Sequence_SuccessCase() {
        $peg = new Peg(['S' => new Sequence(new TerminalSymbol('abc'), new TerminalSymbol('def'))]);
        $res = $peg->parse('abcdefghi');
        $this->assertEquals('abcdef', $res);
    }

    public function dataParse_Sequence_FailureCase() {
        return [
            [[new TerminalSymbol('abc'), new TerminalSymbol('abc')], 'abc'],
            [[new TerminalSymbol('efg'), $this->newRaiseErrorTerminalSymbol()], 'abc'],
            [[new TerminalSymbol('abc'), new TerminalSymbol('xyz')], 'abcefg'],
        ];
    }

    private function newRaiseErrorTerminalSymbol() {
        return new class extends TerminalSymbol {
            public function __construct() {
            }

            public function parse($input, Peg $peg) {
                throw new \RuntimeException();
            }
        };
    }

    /**
     * @dataProvider dataParse_Sequence_FailureCase
     */
    public function testParse_Sequence_FailureCase(array $seq, string $input) {
        $peg = new Peg(['S' => new Sequence(...$seq)]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function dataParse_Choice() {
        return [
            ['foo', [new TerminalSymbol('foo'), $this->newRaiseErrorTerminalSymbol()], 'foobar'],
            ['foo', [new TerminalSymbol('xyz'), new TerminalSymbol('foo')], 'foobar'],
        ];
    }

    /**
     * @dataProvider dataParse_Choice
     */
    public function testParse_Choice($expected, array $variants, string $input) {
        $peg = new Peg(['S' => new Choice(...$variants)]);
        $res = $peg->parse($input);
        $this->assertEquals($expected, $res);
    }

    public function dataZeroOrMoreRepetitions_RepetitionCase() {
        return [['foo', 'foo', 'foobar'], ['foofoo', 'foo', 'foofoobar']];
    }

    /**
     * @dataProvider dataZeroOrMoreRepetitions_RepetitionCase
     */
    public function testZeroOrMoreRepetitions_RepetitionCase($expected, string $terminal, string $input) {
        $peg = new Peg(['S' => new ZeroOrMoreRepetition(new TerminalSymbol($terminal))]);
        $res = $peg->parse($input);
        $this->assertEquals($expected, $res);
    }

    public function testZeroOrMoreRepetitions_TerminationCase() {
        $peg = new Peg(['S' => new ZeroOrMoreRepetition(new TerminalSymbol('foo'))]);
        $res = $peg->parse('bar');
        $this->assertEquals('', $res);
    }

    public function testNotPredicate_Case1_InIsolation() {
        /*
        if (e, xy) => (n, x)
        then (!e, xy) => (n + 1, f)
        If expression e succeeds consuming input x, then the syntactic predicate !e fails.
        */
        $input = 'foo';
        $this->assertFalse((new NotPredicate(new TerminalSymbol($input)))->parse($input, new Peg([])));
    }

    public function testNotPredicate_Case1() {
        $input = 'foo';
        $peg = new Peg(['S' => new NotPredicate(new TerminalSymbol($input))]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function testNotPredicate_Case2_InIsolation() {
        /*
        if (e, x) => (n, f)
        then (!e, x) => (n + 1, E).
        If e fails, then !e succeeds but consumes nothing.
        */
        $expression = new NotPredicate(new TerminalSymbol('foo'));
        $this->assertSame('', $expression->parse('bar', new Peg([])));
    }

    public function testNonPredicate_Case2() {
        $peg = new Peg(['S' => new NotPredicate(new TerminalSymbol('bar'))]);
        $res = $peg->parse('foo');
        $this->assertEquals('', $res);
    }

    public function testArithmeticGrammar() {
        $this->markTestIncomplete();
        // Modified grammar from pegjs
        $peg = new Peg(
            [
                'Expression' => new Sequence(
                    new NonterminalSymbol('Term'),
                    new Choice(new TerminalSymbol('+'), new TerminalSymbol('+')),
                    new NonterminalSymbol('Term')
                ),
                'Term'       => new Sequence(
                    new NonterminalSymbol('Factor'),
                    new Choice(new TerminalSymbol('*'), new TerminalSymbol('/')),
                    new NonterminalSymbol('Factor')
                ),
                'Factor'     => new Choice(
                    new Sequence(new TerminalSymbol('('), new NonterminalSymbol('Expression'), new TerminalSymbol(')')),
                    new NonterminalSymbol('Integer')
                ),
                'Integer'    => new OneOrMoreRepetition(new CharacterClass('[0-9]')),
            ]
        );
        $this->assertSame('14', $peg->parse('2*(3+4)'));
        // @TODO: Add whitespaces
        /*
        // Accepts expressions like "2 * (3 + 4)" and computes their value.

        Expression
         = head:Term tail:(_ ("+" / "-") _ Term)* {
             return tail.reduce(function(result, element) {
               if (element[1] === "+") { return result + element[3]; }
               if (element[1] === "-") { return result - element[3]; }
             }, head);
           }

        Term
         = head:Factor tail:(_ ("*" / "/") _ Factor)* {
             return tail.reduce(function(result, element) {
               if (element[1] === "*") { return result * element[3]; }
               if (element[1] === "/") { return result / element[3]; }
             }, head);
           }

        Factor
         = "(" _ expr:Expression _ ")" { return expr; }
         / Integer

        Integer "integer"
         = _ [0-9]+ { return parseInt(text(), 10); }

        _ "whitespace"
         = [ \t\n\r]*
        */
    }
}