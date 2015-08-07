<?php
namespace MorphoTest\Cli;

require_once LIB_DIR_PATH . '/Morpho/Cli/shell-functions.php';

use Morpho\Test\TestCase;

class ShellFunctionsTest extends TestCase {
    public function testPrintLnSingle() {
        ob_start();
        printLn("Printed");
        $this->assertEquals("Printed\n", ob_get_clean());
    }

    public function testPrintLnMultiple() {
        ob_start();
        printLn("bee", "ant");
        $this->assertEquals("bee\nant\n", ob_get_clean());
    }

    public function testGetOptionalArg() {
        $this->assertEquals(null, getOptionalArg([], 'foo'));
        $this->assertEquals('', getOptionalArg(['foo' => ''], 'foo'));
        $this->assertEquals('bar', getOptionalArg(['foo' => 'bar'], 'foo'));
        $this->assertEquals('0', getOptionalArg(['foo' => '0'], 'foo'));
        $this->assertEquals('-1', getOptionalArg(['foo' => '-1'], 'foo'));
    }

    public function testGetBoolArg() {
        $this->assertEquals(false, getBoolArg([], 'foo'));
        $this->assertEquals(true, getBoolArg(['foo' => ''], 'foo'));
        $this->assertEquals(true, getBoolArg(['foo' => 'bar'], 'foo'));
        $this->assertEquals(true, getBoolArg(['foo' => '0'], 'foo'));
    }

    public function testLogArgs() {
        $fn = longArgs(['foo' => 'bar', 'some']);
        $this->assertEquals("--foo='bar' --some", $fn());
    }

    public function testAskYesNo() {
        $this->markTestIncomplete();
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }
}