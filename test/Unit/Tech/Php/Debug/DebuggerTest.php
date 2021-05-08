<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php\Debug;

use Morpho\Base\Env;
use Morpho\Tech\Php\Debug\Debugger;
use Morpho\Testing\TestCase;

use function ob_get_clean;
use function ob_start;

class DebuggerTest extends TestCase {
    private Debugger $debugger;

    public function setUp(): void {
        $this->debugger = Debugger::instance();
    }

    public function testVarToStr_FixOutput() {
        $this->checkXdebug();
        $this->assertEquals("\nstring(3) \"<=>\"\n", $this->debugger->varToStr('<=>'));
    }

    public function testVarToStr() {
        $this->checkXdebug();

        ob_start();
        ?>

        array(1) {
        ["foo"] => string(3) "bar"
        }
        <?php
        $expected = ob_get_clean();
        $this->assertEquals($expected, $this->debugger->varToStr(['foo' => 'bar']));
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

    public function tearDown(): void {
        Debugger::resetState();
    }

    private function checkXdebug(): void {
        if (Env::isXdebugEnabled() && Env::boolIniVal('xdebug.overload_var_dump')) {
            $this->markTestIncomplete();
        }
    }
}
