<?php
declare(strict_types=1);

namespace MorphoTest\Code;

use Morpho\Code\StringScanner;
use Morpho\Test\TestCase;

/**
 * Tests based on examples found at http://ruby-doc.org/stdlib-2.4.1/libdoc/strscan/rdoc/StringScanner.html
 */
class StringScannerTest extends TestCase {
    public function testChar() {
        $s = 'ab';
        $scanner = new StringScanner($s);

        $this->assertEquals('a', $scanner->char());
        $this->assertEquals(1, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertEquals('b', $scanner->char());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->char());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $scanner = new StringScanner("\244\242", 'EUC-JP'); # Japanese hira-kana "A" in EUC-JP
        $this->assertEquals("\244\242", $scanner->char());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());

        $this->assertNull($scanner->char());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());
    }

    public function testByte() {
        $scanner = new StringScanner("\244\242", 'EUC-JP'); # Japanese hira-kana "A" in EUC-JP
        $this->assertEquals("\244", $scanner->byte());
        $this->assertEquals(1, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());

        $this->assertEquals("\242", $scanner->byte());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());

        $this->assertNull($scanner->byte());
        $this->assertEquals(2, $scanner->offset());
        $this->assertEquals(1, $scanner->charOffset());
    }

    public function testScan() {
        $s = 'This is an example string';

        $scanner = new StringScanner($s);

        $re = function ($re) {
            return '~' . $re . '~sA';
        };

        $this->assertFalse($scanner->eos());
        $this->assertEquals(0, $scanner->offset());

        $this->assertEquals('This', $scanner->scan($re('\w+')));
        $this->assertEquals(4, $scanner->offset());

        $this->assertNull($scanner->scan($re('\w+')));
        $this->assertEquals(4, $scanner->offset());

        $this->assertEquals(' ', $scanner->scan($re('\s+')));
        $this->assertEquals(5, $scanner->offset());

        $this->assertNull($scanner->scan($re('\s+')));
        $this->assertEquals(5, $scanner->offset());

        $this->assertEquals('is', $scanner->scan($re('\w+')));
        $this->assertEquals(7, $scanner->offset());

        $this->assertFalse($scanner->eos());

        $this->assertEquals(' ', $scanner->scan($re('\s+')));

        $this->assertEquals('an', $scanner->scan($re('\w+')));
        $this->assertEquals(' ', $scanner->scan($re('\s+')));
        $this->assertEquals('example', $scanner->scan($re('\w+')));
        $this->assertEquals(' ', $scanner->scan($re('\s+')));
        $this->assertEquals('string', $scanner->scan($re('\w+')));
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->scan($re('\s+')));
        $this->assertNull($scanner->scan($re('\w+')));
    }
}