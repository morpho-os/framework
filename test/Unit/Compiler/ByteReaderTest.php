<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use Morpho\Compiler\ByteReader;
use Morpho\Compiler\IStringReader;
use Morpho\Testing\TestCase;

/**
 * Based on [stringscanner from ruby](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html), see [license](https://github.com/ruby/ruby/blob/master/COPYING).
 * In particular on:
 *     * https://github.com/ruby/ruby/blob/ffd0820ab317542f8780aac475da590a4bdbc7a8/test/strscan/test_stringscanner.rb
 *     * https://github.com/ruby/ruby/tree/ffd0820ab317542f8780aac475da590a4bdbc7a8/spec/ruby/library/stringscanner/
 */
class ByteReaderTest extends TestCase {
    public function testInputAccessors() {
        $input = 'test string';
        $reader = new ByteReader($input);
        $this->assertInstanceOf(IStringReader::class, $reader);
        $this->assertSame($input, $reader->input());

        $reader->read('/test/');
        $this->checkState($reader, 4, 'test');

        $input = 'a';
        $reader->setInput($input);
        $this->assertSame($input, $reader->input());
        $this->checkState($reader, 0, null);
        //$this->assertSame(0, $reader->charOffset());
        $this->assertTrue($reader->isBol());

        $reader->read('/a/');
        $reader->setInput('b');
        $this->checkState($reader, 0, null);
        //$this->assertSame(0, $reader->charOffset());
    }

    public function testConcat() {
        $reader = new ByteReader('a');

        $reader->read('/a/');

        $reader->concat('b');

        $this->checkState($reader, 1, 'a');
        $this->assertFalse($reader->isEnd());
        $this->assertSame('b', $reader->read('/b/'));
        $this->assertTrue($reader->isEnd());

        $reader->concat('c');

        $this->checkState($reader, 2, 'b');
        $this->assertFalse($reader->isEnd());
        $this->assertSame('c', $reader->read('/c/'));
        $this->assertTrue($reader->isEnd());
    }

    public function testOffsetAccessors() {
        $reader = new ByteReader('test string');
        $this->assertSame(0, $reader->offset());

        $reader->readByte();

        $this->assertSame(1, $reader->offset());

        $reader->readByte();

        $this->assertSame(2, $reader->offset());

        $reader->terminate();

        $this->assertSame(11, $reader->offset());

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertVoid($reader->setOffset(7));
        $this->assertSame('ring', $reader->rest());
    }

    public function testRead() {
        $reader = new ByteReader('stra strb strc');

        $this->assertSame('stra', $reader->read('/\w+/'));
        $this->checkState($reader, 4, 'stra');

        $this->assertSame(' ', $reader->read('/\s+/'));
        $this->checkState($reader, 5, ' ');

        $this->assertSame('strb', $reader->read('/\w+/'));
        $this->checkState($reader, 9, 'strb');

        $this->assertSame(' ', $reader->read('/\s+/'));
        $this->checkState($reader, 10, ' ');

        $this->assertSame('strc', $reader->read('/\w+/'));
        $this->checkState($reader, 14, 'strc');

        $this->assertNull($reader->read('/\w+/'));
        $this->checkState($reader, 14, null);

        $reader = new ByteReader('');

        $this->assertSame("", $reader->read('//'));
        $this->checkState($reader, 0, '');

        $this->assertNull($reader->read('/\w+/'));
        $this->checkState($reader, 0, null);
    }

    public function testCheck() {
        $reader = new ByteReader("Foo Bar Baz");

        $this->assertSame("Foo", $reader->check('/Foo/'));
        $this->checkState($reader, 0, 'Foo');

        $this->assertNull($reader->check('/Bar/'));
        $this->checkState($reader, 0, null);
    }

    public function testCheckUntil() {
        $reader = new ByteReader("Foo Bar Baz");
        $this->assertSame("Foo", $reader->checkUntil('/Foo/'));
        $this->checkState($reader, 0, 'Foo');

        $this->assertSame("Foo Bar", $reader->checkUntil('/Bar/'));
        $this->checkState($reader, 0, 'Bar');

        $this->assertNull($reader->checkUntil('/Qux/'));
        $this->checkState($reader, 0, null);
    }







    public function testRead_Full() {
        $reader = new ByteReader("Foo Bar Baz");
        $this->assertSame(4, $reader->read('/Foo /', false, false));
        $this->assertSame(0, $reader->offset());
        $this->assertNull($reader->read('/Baz/', false, false));

        $this->assertSame("Foo ", $reader->read('/Foo /', false, true));
        $this->assertSame(0, $reader->offset());

        $this->assertNull($reader->read('/Baz/', false, false));

        $this->assertSame(4, $reader->read('/Foo /', true, false));
        $this->assertSame(4, $reader->offset());

        $this->assertNull($reader->read('/Baz /', false, false));
        $this->assertSame("Bar ", $reader->read('/Bar /', true, true));

        $this->assertSame(8, $reader->offset());
        $this->assertNull($reader->read('/az/', false, false));
    }

    public function testReadUntil() {
        $reader = new ByteReader("Foo Bar Baz");
        $this->assertSame("Foo", $reader->readUntil('/Foo/'));
        $this->checkState($reader, 3, 'Foo');

        $reader = new ByteReader("Fri Dec 12 1975 14:39");
        $this->assertSame('Fri Dec 1', $reader->readUntil('/1/'));
        $this->assertSame("Fri Dec ", $reader->preMatch());
        $this->assertNull($reader->readUntil('/XYZ/'));
    }
    
    public function testReadUntil_Full() {
        $reader = new ByteReader("Foo Bar Baz");
        
        $this->assertSame(8, $reader->readUntil('/Bar /', false, false));
        $this->assertSame(0, $reader->offset());

        $this->assertSame("Foo Bar ", $reader->readUntil('/Bar /', false, true));
        $this->assertSame(0, $reader->offset());
        $this->assertSame(8, $reader->readUntil('/Bar /', true, false));
        $this->assertSame(8, $reader->offset());
        $this->assertSame("Baz", $reader->readUntil('/az/', true, true));
        $this->assertSame(11, $reader->offset());
    }

    public function testSkip() {
        $reader = new ByteReader('stra strb strc');

        $checkOffsetMatched = fn ($offset, $matched) => $this->checkState($reader, $offset, $matched);

        $this->assertSame(4, $reader->skip('/\w+/'));
        $checkOffsetMatched(4, 'stra');

        $this->assertSame(1, $reader->skip('/\s+/'));
        $checkOffsetMatched(5, ' ');

        $this->assertSame(4, $reader->skip('/\w+/'));
        $checkOffsetMatched(9, 'strb');

        $this->assertSame(1, $reader->skip('/\s+/'));

        $this->assertSame(4, $reader->skip('/\w+/'));
        $checkOffsetMatched(14, 'strc');

        $this->assertNull($reader->skip('/\w+/'));
        $checkOffsetMatched(14, null);

        $this->assertNull($reader->skip('/\s+/'));
        $checkOffsetMatched(14, null);

        $this->assertTrue($reader->isEnd());
        $checkOffsetMatched(14, null);
    }

    public function testSkipUntil() {
        $reader = new ByteReader("Foo Bar Baz");
        $this->assertSame(3, $reader->skipUntil('/Foo/'));
        $this->assertSame(3, $reader->offset());
        $this->assertSame(4, $reader->skipUntil('/Bar/'));
        $this->assertSame(7, $reader->offset());
        $this->assertNull($reader->skipUntil('/Qux/'));
    }

    public function testPeek() {
        $reader = new ByteReader("test string");
        $this->assertSame('', $reader->peek(0));
        $this->checkState($reader, 0, null);

        $this->assertSame("test st", $reader->peek(7));
        $this->checkState($reader, 0, null);

        $this->assertSame("test st", $reader->peek(7));
        $this->checkState($reader, 0, null);

        $reader->read('/test/');

        $this->assertSame(" stri", $reader->peek(5));
        $this->checkState($reader, 4, 'test');

        $this->assertSame(" string", $reader->peek(10));
        $this->checkState($reader, 4, 'test');

        $reader->read('/ string/');
        $this->assertSame("", $reader->peek(10));
        $this->checkState($reader, 11, ' string');
    }








    public function testIsEnd() {
        $reader = new ByteReader('test string');

        $this->assertFalse($reader->isEnd());

        $reader->read('~\w+~');

        $this->assertFalse($reader->isEnd());

        $reader->read('~\s+~');
        $reader->read('~\w+~');

        $this->assertTrue($reader->isEnd());

        $reader->read('~\w+~');

        $this->assertTrue($reader->isEnd());
    }

    public function testIsBol() {
        $reader = new ByteReader("a\nbbb\n\ncccc\nddd\r\neee");

        $this->assertTrue($reader->isBol());

        $reader->read('/a/');

        $this->assertFalse($reader->isBol());

        $reader->read('/\n/');

        $this->assertTrue($reader->isBol());
            
        $reader->read('/b/');
        
        $this->assertFalse($reader->isBol());
        
        $reader->read('/b/');
        
        $this->assertFalse($reader->isBol());
        
        $reader->read('/b/');
        
        $this->assertFalse($reader->isBol());
        
        $reader->read('/\n/');
        
        $this->assertTrue($reader->isBol());

        $reader->unread();

        $this->assertFalse($reader->isBol());
        
        $reader->read('/\n/');
        $reader->read('/\n/');
        
        $this->assertTrue($reader->isBol());

        $reader->read('/c+\n/');

        $this->assertTrue($reader->isBol());

        $reader->read('/d+\r\n/');

        $this->assertTrue($reader->isBol());

        $reader->read('/e+/');

        $this->assertFalse($reader->isBol());
    }






    


    public function testSkip_EmptyStr() {
        $reader = new ByteReader("");

        $this->assertSame(0, $reader->skip('//'));
        $this->checkState($reader, 0, '');

        $this->assertSame(0, $reader->skip('//'));
        $this->checkState($reader, 0, '');
    }

    public function testSkip_MultiLineModifier() {
        $reader = new ByteReader("a\nbc");
        $this->assertSame(2, $reader->skip('/a\n/m'));
        $this->assertSame(1, $reader->skip('/^b/m'));
        $this->assertSame(1, $reader->skip('/c/'));
    }
    
    public function testReadByte() {
        $reader = new ByteReader('abcde');
        $this->assertSame('a', $reader->readByte());
        $this->checkState($reader, 1, 'a');

        $this->assertSame('b', $reader->readByte());
        $this->assertSame('c', $reader->readByte());
        $this->assertSame('d', $reader->readByte());
        $this->assertSame('e', $reader->readByte());
        $this->assertNull($reader->readByte());
        
        $reader = new ByteReader("\x00\x01");
        $this->assertSame("\x00", $reader->readByte());
        $this->assertSame("\x01", $reader->readByte());
        $this->assertNull($reader->readByte());
        
        $reader = new ByteReader('');
        $this->assertNull($reader->readByte());
    }

    public function testMatched() {
        $reader = new ByteReader('stra strb strc');

        $reader->read('/\w+/');
        $this->assertSame('stra', $reader->matched());

        $reader->read('/\s+/');
        $this->assertSame(' ', $reader->matched());

        $reader->read('/st/');
        $this->assertSame('st', $reader->matched());

        $reader->read('/\w+/');
        $this->assertSame('rb', $reader->matched());

        $reader->read('/\s+/');
        $this->assertSame(' ', $reader->matched());

        $reader->read('/\w+/');
        $this->assertSame('strc', $reader->matched());

        $reader->read('/\w+/');
        $this->assertNull($reader->matched());

        $reader = new ByteReader('ab');
        $reader->readByte();
        $this->assertSame('a', $reader->matched());
        $reader->readByte();
        $this->assertSame('b', $reader->matched());
        $reader->readByte();
        $this->assertNull($reader->matched());

        $reader = new ByteReader('abc');
        $reader->skip('/./');
        $this->assertSame('a', $reader->matched());
    }

    public function testPreMatch() {
        $reader = new ByteReader('a b c d e');

        $reader->read('/\w/');
        $this->assertSame('', $reader->preMatch());

        $reader->skip('/\s/');
        $this->assertSame('a', $reader->preMatch());

        $reader->read('/b/');
        $this->assertSame('a ', $reader->preMatch());

        $this->assertSame(' c', $reader->readUntil('/c/'));
        $this->assertSame('a b ', $reader->preMatch());

        $this->assertSame(' ', $reader->readByte());
        $this->assertSame('a b c', $reader->preMatch());

        $reader->readByte();
        $this->assertSame('a b c ', $reader->preMatch());

        $reader->readByte();
        $this->assertSame('a b c d', $reader->preMatch());

        $reader->read('/never match/');
        $this->assertNull($reader->preMatch());
    }

    public function testOffsettMatch() {
        $reader = new ByteReader('a b c d e');

        $reader->read('/\w/');
        $this->assertSame(' b c d e', $reader->postMatch());

        $reader->skip('/\s/');
        $this->assertSame('b c d e', $reader->postMatch());

        $reader->read('/b/');
        $this->assertSame(' c d e', $reader->postMatch());

        $reader->readUntil('/c/');
        $this->assertSame(' d e', $reader->postMatch());

        $reader->readByte();
        $this->assertSame('d e', $reader->postMatch());

        $reader->readByte();
        $this->assertSame(' e', $reader->postMatch());

        $reader->readByte();
        $this->assertSame('e', $reader->postMatch());

        $reader->read('/never match/');
        $this->assertNull($reader->postMatch());

        $reader->read('/./');
        $this->assertSame('', $reader->postMatch());

        $reader->read('/./');
        $this->assertNull($reader->postMatch());
    }

    public function testTerminate() {
        $reader = new ByteReader('');
        $reader->terminate();
        $this->assertTrue($reader->isEnd());

        $reader = new ByteReader('ssss');

        $reader->readByte();

        $reader->terminate();
        $this->assertTrue($reader->isEnd());

        $reader->terminate();
        $this->assertTrue($reader->isEnd());
    }

    public function testReset() {
        $reader = new ByteReader('ssss');
        $reader->readByte();

        $reader->reset();
        $this->assertSame(0, $reader->offset());

        $this->assertSame(null, $reader->matched());
        $this->assertSame(null, $reader->preMatch());
        $this->assertSame(null, $reader->postMatch());

        $reader->read('/\w+/');
        $reader->reset();
        $this->assertSame(0, $reader->offset());

        $reader->reset();
        $this->assertSame(0, $reader->offset());
    }

    public function testMatchedLen() {
        $reader = new ByteReader('test string');

        $this->assertNull($reader->matchedLen());

        $reader->read('/test/');
        $this->assertSame(4, $reader->matchedLen());
        $this->assertSame(4, $reader->matchedLen());

        $reader->read('//');
        $this->assertSame(0, $reader->matchedLen());

        $reader->read('/x/');
        $this->assertNull($reader->matchedLen());
        $this->assertNull($reader->matchedLen());

        $reader->terminate();
        $this->assertNull($reader->matchedLen());

        $reader = new ByteReader('test string');
        $this->assertNull($reader->matchedLen());

        $reader->read('/test/');
        $this->assertSame(4, $reader->matchedLen());

        $reader->terminate();
        $this->assertNull($reader->matchedLen());
    }
    
    public function testMatch() {
        $reader = new ByteReader("test string");
        $this->assertSame(4, $reader->match('/\w+/'));
        $this->assertSame(4, $reader->match('/\w+/'));
        $this->assertNull($reader->match('/\s+/'));
    }

    public function testLookUntil() {
        $reader = new ByteReader("test string");
        $this->assertSame(3, $reader->lookUntil('/s/'));
        $this->assertSame(0, $reader->offset());
        $reader->read('/test/');
        $this->assertSame(2, $reader->lookUntil('/s/'));
        $this->assertSame(4, $reader->offset());
        $this->assertNull($reader->lookUntil('/e/'));
    }



    public function testUnscan() {
        $this->markTestIncomplete();
/*    $reader = new ByteReader('test string')
    $this->assertSame("test", $reader->read('/\w+/'))
    $reader->unscan
    $this->assertSame("te", $reader->read('/../'))
    $this->assertNull($reader->read('/\d/'))
    assert_raise(ScanError) { $reader->unscan }

todo: throw exception if can't unscan
s = StringScanner.new('test string')
s.read('/\w+/')        # => "test"
s.unscan
s.read('/../')         # => "te"
s.read('/\d/')         # => nil
s.unscan             # ScanError: unscan failed: previous match record not exist*/

    }

    public function testRest() {
        $reader = new ByteReader('test string');
        $this->assertSame("test string", $reader->rest());
        $reader->read('/test/');
        $this->assertSame(" string", $reader->rest());
        $reader->read('/ string/');
        $this->assertSame("", $reader->rest());
        $reader->read('/ string/');
    }

    public function testRestLen() {
        $this->markTestIncomplete();
      //def test_rest_size
        /*$reader = new ByteReader('test string')
        $this->assertSame(11, $reader->rest_size)
        $reader->read('/test/')
        $this->assertSame(7, $reader->rest_size)
        $reader->read('/ string/')
        $this->assertSame(0, $reader->rest_size)
        $reader->read('/ string/')*/
    }

    public function testLen() {
        $this->markTestIncomplete();
      /*def test_size
        $reader = new ByteReader("Fri Dec 12 1975 14:39")
        $reader->read('/(\w+) (\w+) (\d+) /')
        $this->assertSame(4, $reader->size)
      end*/
    }

    public function testCaptures() {
        $this->markTestIncomplete();
          /*def test_captures
            $reader = new ByteReader("Timestamp: Fri Dec 12 1975 14:39")
            $reader->read("Timestamp: ")
            $reader->read('/(\w+) (\w+) (\d+) /')
            $this->assertSame(["Fri", "Dec", "12"], $reader->captures)
            $reader->read('/(\w+) (\w+) (\d+) /')
            assert_nil(s.captures)
          end*/
    }

    public function testValuesAt() {
        $this->markTestIncomplete();
/*
  def test_values_at
    $reader = new ByteReader("Timestamp: Fri Dec 12 1975 14:39")
    $reader->read("Timestamp: ")
    $reader->read('/(\w+) (\w+) (\d+) /')
    $this->assertSame(["Fri Dec 12 ", "12", nil, "Dec"], $reader->values_at(0, -1, 5, 2))
    $reader->read('/(\w+) (\w+) (\d+) /')
    assert_nil(s.values_at(0, -1, 5, 2))
  end*/

    }

    private function checkState(ByteReader $reader, int $offset, ?string $matched): void {
        $this->assertSame($offset, $reader->offset());
        $this->assertSame($matched, $reader->matched());
    }
}