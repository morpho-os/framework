<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Code\Parsing;

use Morpho\Code\Parsing\StringReader;
use Morpho\Code\Parsing\SyntaxError;
use Morpho\Test\TestCase;

class StringReaderTest extends TestCase {
    public function dataForPeekN_ValidNumberOfBytes() {
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
     * @dataProvider dataForPeekN_ValidNumberOfBytes
     */
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
     * @dataProvider dataForPeekN_ValidNumberOfBytes
     */
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
}