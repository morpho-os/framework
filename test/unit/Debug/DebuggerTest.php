<?php declare(strict_types=1);
namespace MorphoTest\Debug;

use Morpho\Test\TestCase;
use Morpho\Debug\Debugger;
use Morpho\Base\Environment;

class DebuggerTest extends TestCase {
    public function setUp() {
        $this->debugger = Debugger::instance();
    }

    public function testVarToString() {
        if (Environment::isXdebugEnabled() && Environment::boolIniVal('xdebug.overload_var_dump')) {
            $this->markTestIncomplete();
        }
        ob_start();
?>

array(1) {
  ["foo"] => string(3) "bar"
}
<?php
        $expected = ob_get_clean();
        $this->assertEquals($expected, $this->debugger->varToString(['foo' => 'bar']));
    }

    public function testIsHtmlMode() {
        $this->checkBoolAccessor([$this->debugger, 'isHtmlMode'], false);
    }

    public function testCalledAt() {
        ob_start();
?>

Debugger called at [<?= __FILE__ ?>:<?= __LINE__ + 3 ?>]
<?php
        $expected = ob_get_clean();
        $this->assertEquals($expected, $this->debugger->calledAt());
    }

    public function tearDown() {
        Debugger::resetState();
    }
}
