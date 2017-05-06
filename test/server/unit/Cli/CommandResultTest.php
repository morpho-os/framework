<?php declare(strict_types=1);
namespace MorphoTest\Cli;

use Morpho\Test\TestCase;
use Morpho\Cli\CommandResult;

class CommandResultTest extends TestCase {
    public function testLines_DefaultArgs() {
        $res = new CommandResult('foo', 0, <<<OUT
 First line

        Second line

        Third line
        
OUT
        );

        $this->assertEquals(
            [
                'First line',
                'Second line',
                'Third line'
            ],
            iterator_to_array($res->lines())
        );
    }

    public function testInterfaces() {
        $this->assertInstanceOf(\IteratorAggregate::class, new CommandResult('foo', 0, ''));
    }
}