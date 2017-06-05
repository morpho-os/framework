<?php
declare(strict_types=1);

namespace MorphoTest\Code;

use Morpho\Code\StringReader;
use Morpho\Code\SyntaxError;
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
        $this->assertFalse($reader->read1());

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
    
    public function testSkip_NotMatching() {
        $reader = new StringReader('foo');
        $this->expectException(SyntaxError::class, "Read input doesn't match expected input");
        $reader->skip('bar');
    }

    public function testSkip_Matching() {
        $reader = new StringReader('foo');
        $reader->skip('');
        $this->assertEquals(0, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
        $reader->skip('f');
        $this->assertEquals(1, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
        $reader->skip('oo');
        $this->assertEquals(3, $reader->offset());
        $this->assertEquals(1, $reader->lineNo());
    }
}