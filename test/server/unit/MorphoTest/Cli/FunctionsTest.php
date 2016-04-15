<?php
namespace MorphoTest\Cli;

require_once LIB_DIR_PATH . '/Morpho/Cli/functions.php';

use Morpho\Test\TestCase;
use function Morpho\Cli\{
    escapeEachArg, argString
};

class FunctionsTest extends TestCase {
    public function testAskYesNo() {
        $this->markTestIncomplete();
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }

    public function testEscapedArgs() {
        $this->assertEquals(["'foo'\\''bar'", "'test/'"], \Morpho\Cli\escapedArgs(["foo'bar", 'test/']));
    }

    public function testEscapedArgsString() {
        $this->assertEquals("'foo'\\''bar' 'test/'", \Morpho\Cli\escapedArgsString(["foo'bar", 'test/']));
    }
}