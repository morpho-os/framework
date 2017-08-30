<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Cli;

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

    public function testInterface() {
        $this->assertInstanceOf(\IteratorAggregate::class, new CommandResult('foo', 0, ''));
    }
}