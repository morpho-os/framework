<?php
namespace MorphoTest\Cli;

use Morpho\Test\TestCase;
use Morpho\Cli\CommandResult;

class CommandResultTest extends TestCase {
    public function testToLines_DefaultArgs() {
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
            iterator_to_array($res->toLines())
        );
    }
}