<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use Morpho\Compiler\MbReader;

/**
 * Based on [stringscanner from ruby](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html), see [license](https://github.com/ruby/ruby/blob/master/COPYING)
 * Multi-byte reader supports all tests of the ByteReader and extends them with tests for multi-byte encodings.
 */
class MbReaderTest extends ByteReaderTest {
    public function setUp(): void {
        parent::setUp();
        $this->markTestIncomplete();
    }

    public function testMbConstruct() {



    }

/*

def test_getch
$reader = new ByteReader('abcde')
$this->assertSame('a', $reader->getch
$this->assertSame('b', $reader->getch
$this->assertSame('c', $reader->getch
$this->assertSame('d', $reader->getch
$this->assertSame('e', $reader->getch
assert_nil        $reader->getch

$reader = new ByteReader("\244\242".dup.force_encoding("euc-jp"))
$this->assertSame("\244\242".dup.force_encoding("euc-jp"), $reader->getch
assert_nil $reader->getch

$reader = new ByteReader('test'.dup)
$reader->read('/te/')
$reader->string.replace ''
$this->assertSame(nil, $reader->getch
end

def test_encoding
    ss = new ByteReader("\xA1\xA2".dup.force_encoding("euc-jp"))
    assert_equal(Encoding::EUC_JP, ss.read('/./e).encoding)
  end

  def test_encoding_string
    str = "\xA1\xA2".dup.force_encoding("euc-jp")
    ss = new ByteReader(str)
    assert_equal(str.dup, ss.read(str.dup))
  end

  def test_invalid_encoding_string
    str = "\xA1\xA2".dup.force_encoding("euc-jp")
    ss = new ByteReader(str)
    assert_raise(Encoding::CompatibilityError) do
    ss.read(str.encode("UTF-8"))
    end
  end


  def test_generic_regexp
    ss = new ByteReader("\xA1\xA2".dup.force_encoding("euc-jp"))
    t = ss.read('/./')
    assert_equal("\xa1\xa2".dup.force_encoding("euc-jp"), t)
  end
*/
    /*
def test_pos_unicode
s = create_string_scanner("abcädeföghi")
assert_equal 0, $reader->charpos
assert_equal "abcä", $reader->scan_until(/ä/)
assert_equal 4, $reader->charpos
assert_equal "defö", $reader->scan_until(/ö/)
assert_equal 8, $reader->charpos
$reader->terminate
assert_equal 11, $reader->charpos
end
    */

    ////////////////////////////////////

    public function testChar_Ascii() {
        $this->markTestIncomplete();
        $s = 'ab';
        $scanner = new StringScanner($s);

        $this->assertSame('a', $scanner->char());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->char());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->char());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());
    }

    public function testChar_Unicode() {
        $this->markTestIncomplete();
        $scanner = new StringScanner("\244\242", 'EUC-JP'); # Japanese hira-kana "A" in EUC-JP

        $this->assertSame("\244\242", $scanner->char());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());

        $this->assertNull($scanner->char());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
    }

    public function testByte_Ascii() {
        $s = 'ab';
        $scanner = new StringScanner($s);

        $this->assertSame('a', $scanner->byte());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->byte());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->byte());
        $this->assertSame(2, $scanner->offset());

        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());
    }

    public function testByte_Unicode() {
        $this->markTestIncomplete();
        $scanner = new StringScanner("\244\242", 'EUC-JP'); # Japanese hira-kana "A" in EUC-JP
        $this->assertSame("\244", $scanner->byte());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());

        $this->assertSame("\242", $scanner->byte());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());

        $this->assertNull($scanner->byte());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
    }

    public function dataOffsetAndEos_InitialValues() {
        return [
            ['😂'],
            ['ab'],
            ["abcädeföghi"],
        ];
    }

    /**
     * @dataProvider dataOffsetAndEos_InitialValues
     */
    public function testOffsetAndEos_InitialValues($s) {
        $scanner = new StringScanner($s);

        $this->assertSame(0, $scanner->charOffset());
        $this->assertSame(0, $scanner->offset());
        $this->assertFalse($scanner->eos());
    }

    public function testCharAndByte_Unicode1() {
        $this->markTestIncomplete();
        $scanner = new StringScanner("abcädeföghi"); // \x61\x62\x63\xc3\xa4\x64\x65\x66\xc3\xb6\x67\x68\x69

        $this->assertSame('a', $scanner->char());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->char());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('c', $scanner->char());
        $this->assertSame(3, $scanner->offset());
        $this->assertSame(3, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('ä', $scanner->char());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(4, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('d', $scanner->char());
        $this->assertSame(6, $scanner->offset());
        $this->assertSame(5, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('e', $scanner->char());
        $this->assertSame(7, $scanner->offset());
        $this->assertSame(6, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('f', $scanner->char());
        $this->assertSame(8, $scanner->offset());
        $this->assertSame(7, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('ö', $scanner->char());
        $this->assertSame(10, $scanner->offset());
        $this->assertSame(8, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('g', $scanner->char());
        $this->assertSame(11, $scanner->offset());
        $this->assertSame(9, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('h', $scanner->char());
        $this->assertSame(12, $scanner->offset());
        $this->assertSame(10, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('i', $scanner->char());
        $this->assertSame(13, $scanner->offset());
        $this->assertSame(11, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->char());
        $this->assertSame(13, $scanner->offset());
        $this->assertSame(11, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $scanner->reset();

        $this->assertSame(0, $scanner->offset());
        $this->assertSame(0, $scanner->charOffset());

        $this->assertSame('a', $scanner->byte());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->byte());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('c', $scanner->byte());
        $this->assertSame(3, $scanner->offset());
        $this->assertSame(3, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\xc3", $scanner->byte());
        $this->assertSame(4, $scanner->offset());
        $this->assertSame(4, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\xa4", $scanner->byte());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(4, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('d', $scanner->byte());
        $this->assertSame(6, $scanner->offset());
        $this->assertSame(5, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('e', $scanner->byte());
        $this->assertSame(7, $scanner->offset());
        $this->assertSame(6, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('f', $scanner->byte());
        $this->assertSame(8, $scanner->offset());
        $this->assertSame(7, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\xc3", $scanner->byte());
        $this->assertSame(9, $scanner->offset());
        $this->assertSame(8, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\xb6", $scanner->byte());
        $this->assertSame(10, $scanner->offset());
        $this->assertSame(8, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('g', $scanner->byte());
        $this->assertSame(11, $scanner->offset());
        $this->assertSame(9, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('h', $scanner->byte());
        $this->assertSame(12, $scanner->offset());
        $this->assertSame(10, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('i', $scanner->byte());
        $this->assertSame(13, $scanner->offset());
        $this->assertSame(11, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->byte());
        $this->assertSame(13, $scanner->offset());
        $this->assertSame(11, $scanner->charOffset());
        $this->assertTrue($scanner->eos());
    }

    public function testCharAndByte_Unicode2() {
        $this->markTestIncomplete();
        // 😂 === "\u{1F602}", Supplementary character, 4 bytes "\xf0\x9f\x98\x82"
        $s = '😂b';
        $scanner = new StringScanner($s);

        $this->assertSame('😂', $scanner->char());
        $this->assertSame(4, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->char());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->char());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $scanner->reset();

        $this->assertSame(0, $scanner->offset());
        $this->assertSame(0, $scanner->charOffset());

        $this->assertSame("\xf0", $scanner->byte());
        $this->assertSame(1, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\x9f", $scanner->byte());
        $this->assertSame(2, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\x98", $scanner->byte());
        $this->assertSame(3, $scanner->offset());
        $this->assertSame(3, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame("\x82", $scanner->byte());
        $this->assertSame(4, $scanner->offset());
        $this->assertSame(1, $scanner->charOffset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('b', $scanner->byte());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->byte());
        $this->assertSame(5, $scanner->offset());
        $this->assertSame(2, $scanner->charOffset());
        $this->assertTrue($scanner->eos());
    }

    public function testScan() {
        $this->markTestIncomplete();
        $s = 'This is an example string';

        $scanner = new StringScanner($s);

        $re = function ($re) {
            return '~' . $re . '~sA';
        };

        $this->assertSame('This', $scanner->scan($re('\w+')));
        $this->assertSame(4, $scanner->offset());

        $this->assertNull($scanner->scan($re('\w+')));
        $this->assertSame(4, $scanner->offset());

        $this->assertSame(' ', $scanner->scan($re('\s+')));
        $this->assertSame(5, $scanner->offset());

        $this->assertNull($scanner->scan($re('\s+')));
        $this->assertSame(5, $scanner->offset());

        $this->assertSame('is', $scanner->scan($re('\w+')));
        $this->assertSame(7, $scanner->offset());

        $this->assertFalse($scanner->eos());

        $this->assertSame(' ', $scanner->scan($re('\s+')));

        $this->assertSame('an', $scanner->scan($re('\w+')));
        $this->assertSame(' ', $scanner->scan($re('\s+')));
        $this->assertSame('example', $scanner->scan($re('\w+')));
        $this->assertSame(' ', $scanner->scan($re('\s+')));
        $this->assertSame('string', $scanner->scan($re('\w+')));
        $this->assertTrue($scanner->eos());

        $this->assertNull($scanner->scan($re('\s+')));
        $this->assertNull($scanner->scan($re('\w+')));
    }

    public function testScanUntil_Ascii() {
        $this->markTestIncomplete();
        $s = 'Fri Dec 28 1975 14:39';

        $scanner = new StringScanner($s);

        $this->assertNull($scanner->scanUntil('~XYZ~'));

        $this->assertSame('', $scanner->scanUntil('~~'));
        $this->assertSame(0, $scanner->offset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('Fri Dec 2', $scanner->scanUntil('~2~'));
        $this->assertSame(9, $scanner->offset());
        $this->assertFalse($scanner->eos());

        // @TODO
        //$this->assertSame('Fri Dec ', $scanner->preMatch());

        $this->assertSame('8 1975 ', $scanner->scanUntil('~75\s+~'));
        $this->assertSame(16, $scanner->offset());
        $this->assertFalse($scanner->eos());

        $this->assertSame('14:39', $scanner->scanUntil('~$~'));
        $this->assertSame(21, $scanner->offset());
        $this->assertTrue($scanner->eos());
    }

    public function testScanUntil_Unicode() {
        $this->markTestIncomplete();
        $scanner = new StringScanner("abcädeföghi");
        $this->assertSame("abcä", $scanner->scanUntil('/ä/'));
        $this->assertSame(4, d($scanner->charOffset()));
        $this->assertSame(5, $scanner->offset());

        $this->assertSame("defö", $scanner->scanUntil('/ö/'));
        $this->assertSame(8, $scanner->charOffset());
        $this->assertSame(10, $scanner->offset());
        /*
            s.terminate
            assert_equal 11, s.charpos
                */
    }

    public function testPreMatch() {
        $scanner = new StringScanner('test string');
        $this->markTestIncomplete();

        $this->assertNull($scanner->preMatch());
        //s.scan(/\w+/)           # -> "test"
        //s.scan(/\s+/)           # -> " "
        //s.pre_match             # -> "test"
        //s.post_match            # -> "string"
    }

    public function testPostMatch() {
        $this->markTestIncomplete();
    }

    public function testInputAccessors() {
        $s = 'foo bar';
        $scanner = new StringScanner($s);
        $this->assertSame($s, $scanner->input());

        $s = 'other';
        $this->assertSame($scanner, $scanner->setInput($s));
        $this->assertSame($s, $scanner->input());
    }
    /*

    public function dataPeekN_ValidNumberOfBytes() {
        return [
            [
                '', 0,
            ],
            [
                'foo', 3,
            ],
            [
                'fo', 2,
            ],
        ];
    }

    /**
     * @dataProvider dataPeekN_ValidNumberOfBytes
     * /
    public function testPeekN_ValidNumberOfBytes(string $expected, int $n) {
        $reader = new StringReader('foo');
        $this->assertEquals($expected, $reader->peekN($n));
    }

    public function testPeekN_InvalidNumberOfBytes() {
        $reader = new StringReader('foo');
        $this->expectException(\OutOfBoundsException::class);
        $reader->peekN(4);
    }

    public function testReadN_InputWithEols() {
        $input = <<<OUT
Hello foo
  World bar
ok
OUT;
        $reader = new StringReader($input);
        $this->assertEquals("Hello foo\n  Wo", $reader->readN(14));
        $this->assertEquals(14, $reader->offset());
        $this->assertEquals(2, $reader->lineNo());
    }

    /**
     * @dataProvider dataPeekN_ValidNumberOfBytes
     * /
    public function testReadN_ValidNumberOfBytes(string $expected, int $n) {
        $reader = new StringReader('foo');
        $this->assertEquals($expected, $reader->readN($n));
        $this->assertEquals($n, $reader->offset());
    }

    public function testReadN_InvalidNumberOfBytes() {
        $reader = new StringReader('foo');
        $this->expectException(\OutOfBoundsException::class);
        $reader->readN(4);
    }

    public function testInitialValues() {
        $reader = new StringReader('foo');
        $this->assertEquals(0, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
    }

    public function testRead1AndUnread1() {
        $reader = new StringReader("f\no");
        $this->assertEquals('f', $reader->read1());
        $this->assertEquals(1, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());

        $this->assertEquals("\n", $reader->read1());
        $this->assertEquals(2, $reader->offset());
        $this->assertEquals(2, $reader->lineNo());

        $this->assertEquals("o", $reader->read1());
        $this->assertEquals(3, $reader->offset());
        $this->assertEquals(2, $reader->lineNo());

        // End of string
        $this->assertNull($reader->read1());

        $this->assertEquals('o', $reader->unread1());
        $this->assertEquals(2, $reader->offset());
        $this->assertEquals(2, $reader->lineNo());

        $this->assertEquals("\n", $reader->unread1());
        $this->assertEquals(1, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());

        $this->assertEquals('f', $reader->unread1());
        $this->assertEquals(0, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());

        try {
            $reader->unread1();
            $this->fail();
        } catch (\OutOfBoundsException $e) {
        }
    }
    
    public function testRead_NotMatching() {
        $reader = new StringReader('foo');
        $this->expectException(SyntaxError::class, "Read input doesn't match expected input");
        $reader->read('bar');
    }

    public function testRead_Matching() {
        $reader = new StringReader('foo');
        $reader->read('');
        $this->assertEquals(0, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
        $reader->read('f');
        $this->assertEquals(1, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
        $reader->read('oo');
        $this->assertEquals(3, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
    }

    public function testMatches() {
        $reader = new StringReader('abc');
        $checkPos = function () use ($reader) {
            $this->checkPos($reader, 1, 0);
        };
        $this->assertTrue($reader->matches('~~si'));
        $checkPos();
        $this->assertFalse($reader->matches('~foo~si'));
        $checkPos();
        $this->assertTrue($reader->matches('~ab~si'));
        $checkPos();
        $this->assertTrue($reader->matches('~bc~si'));
        $checkPos();
        $this->assertTrue($reader->matches('~^abc$~si'));
        $checkPos();
    }

    public function testScanAndEos() {
        // Changed example from http://ruby-doc.org/stdlib-2.4.1/libdoc/strscan/rdoc/StringScanner.html

        $s = 'This is an example string';

        $reader = new StringReader($s);

        $re = function ($re) {
            return '~' . $re . '~sA';
        };

        $checkPos = function ($offset) use ($reader) {
            $this->checkPos($reader, 1, $offset);
        };

        $this->assertFalse($reader->eos());
        $checkPos(0);

        $this->assertEquals('This', $reader->scan($re('\w+')));
        $checkPos(4);

        $this->assertNull($reader->scan($re('\w+')));
        $checkPos(4);

        $this->assertEquals(' ', $reader->scan($re('\s+')));
        $checkPos(5);

        $this->assertNull($reader->scan($re('\s+')));
        $checkPos(5);

        $this->assertEquals('is', $reader->scan($re('\w+')));
        $checkPos(7);

        $this->assertFalse($reader->eos());

        $this->assertEquals(' ', $reader->scan($re('\s+')));

        $this->assertEquals('an', $reader->scan($re('\w+')));
        $this->assertEquals(' ', $reader->scan($re('\s+')));
        $this->assertEquals('example', $reader->scan($re('\w+')));
        $this->assertEquals(' ', $reader->scan($re('\s+')));
        $this->assertEquals('string', $reader->scan($re('\w+')));
        $this->assertTrue($reader->eos());

        $this->assertNull($reader->scan($re('\s+')));
        $this->assertNull($reader->scan($re('\w+')));
    }

    private function checkPos(StringReader $reader, int $lineNo, int $offset) {
        $this->assertEquals($offset, $reader->offset());
        $this->assertEquals($lineNo, $reader->lineNo());
    }
    */
}