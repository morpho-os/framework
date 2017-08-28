<?php
declare(strict_types=1);
namespace MorphoTest\Code\Peg;

use Morpho\Code\Peg\Ast;
use Morpho\Code\Peg\EmptyString;
use Morpho\Code\Peg\ParsingExpression;
use Morpho\Code\Peg\Peg;
use Morpho\Code\Peg\Sequence;
use Morpho\Code\Peg\TerminalSymbol;
use Morpho\Code\SyntaxError;
use Morpho\Test\TestCase;


class PegTest extends TestCase {
    public function testInheritanceForExpressions() {
        $expressions = [
            new EmptyString(),
            new TerminalSymbol('foo'),
            new Sequence([new TerminalSymbol('foo')]),
        ];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(ParsingExpression::class, $expression);
        }
    }

    /**
     * Tests below corresponds to chapter "3.3 Interpretation of a Grammar" from http://bford.info/pub/lang/peg.pdf
     */

    public function testParse_EmptyString() {
        $peg = new Peg([
            'S' => new EmptyString()
        ]);
        $ast = $peg->parse('');
        $this->assertEquals(new Ast(['']), $ast);
    }
    
    public function testParse_Terminal_SuccessCase() {
        $peg = new Peg([
            'S' => new TerminalSymbol('a')
        ]);
        $ast = $peg->parse('afoo');
        $this->assertEquals(new Ast(['a']), $ast);
    }

    public function dataForParse_Terminal_FailureCase() {
        return [
            [
                '',
            ],
            [
                'bfoo',
            ],
        ];
    }

    /**
     * @dataProvider dataForParse_Terminal_FailureCase
     */
    public function testParse_Terminal_FailureCase(string $input) {
        $peg = new Peg([
            'S' => new TerminalSymbol('a')
        ]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function testParse_Sequence_SuccessCase() {
        $peg = new Peg([
            'S' => new Sequence([new TerminalSymbol('abc'), new TerminalSymbol('def')])
        ]);
        $ast = $peg->parse('abcdefghi');
        $this->assertEquals(new Ast(['abcdef']), $ast);
    }

    public function dataForParse_Sequence_FailureCase() {
        $raiseErrorTerminal = new class extends TerminalSymbol {
            public function __construct() {
            }
            public function parse($input) {
                throw new \RuntimeException();
            }
        };
        return [
            [
                [new TerminalSymbol('abc'), new TerminalSymbol('abc')], 'abc',  // sequence longer then input
            ],
            [
                [new TerminalSymbol('efg'), $raiseErrorTerminal], 'abc', // if e1 fails, then the all sequence fails without attempting e2 ($raiseErrorTerminal)
            ],
            [
                [new TerminalSymbol('abc'), new TerminalSymbol('xyz')], 'abcefg', // if e1 succeeds but e2 fails, then the sequence should fail.
            ],
        ];
    }

    /**
     * @dataProvider dataForParse_Sequence_FailureCase
     */
    public function testParse_Sequence_FailureCase(array $seq, string $input) {
        $peg = new Peg([
            'S' => new Sequence($seq)
        ]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }
    
    public function testParse_Alternation_Case1() {
        $this->markTestIncomplete();
        /*
        $peg = new Peg([
            'S' => new Alternation()
        ]);*/
    }

    /**
     * ε       : empty string
     * a       : terminal
     * A       : nonterminal
     * e1 e2   : sequence
     * e1 / e2 : prioritized choice/alternation
     * e*      :  >= 0 repetitions
     * !e      : not predicate
     *
     * character classes
     * .       : any character constant
     * ?       : option operator
     * +       : one-or-more repetitions operator
     * &       : and predicate operator
     */
}