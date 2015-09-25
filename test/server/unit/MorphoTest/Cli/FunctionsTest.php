<?php
namespace MorphoTest\Cli;

require_once LIB_DIR_PATH . '/Morpho/Cli/functions.php';

use Morpho\Test\TestCase;
use function Morpho\Cli\{escapeEachArg, argString};

class FunctionsTest extends TestCase {
    public function testAskYesNo() {
        $this->markTestIncomplete();
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }
    
    public function testEscapeEachArg() {
        $this->assertEquals(["'foo'\\''bar'", "'test/'"], \Morpho\Cli\escapeEachArg(["foo'bar", 'test/']));
    }

    public function testArgString() {
        $this->assertEquals("'foo'\\''bar' 'test/'", \Morpho\Cli\argString(["foo'bar", 'test/']));
    }
}