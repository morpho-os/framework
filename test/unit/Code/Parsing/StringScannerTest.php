<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace MorphoTest\Unit\Code\Parsing;

use Morpho\Code\Parsing\StringScanner;
use Morpho\Test\TestCase;

/**
 * Based on the following examples and tests:
 * - http://ruby-doc.org/stdlib-2.4.1/libdoc/strscan/rdoc/StringScanner.html
 * - https://github.com/ruby/ruby/tree/trunk/spec/rubyspec/library/stringscanner
 * - https://github.com/ruby/ruby/blob/trunk/test/strscan/test_stringscanner.rb
 */
class StringScannerTest extends TestCase {
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

    public function dataForOffsetAndEos_InitialValues() {
        return [
            ['😂'],
            ['ab'],
            ["abcädeföghi"],
        ];
    }

    /**
     * @dataProvider dataForOffsetAndEos_InitialValues
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
}