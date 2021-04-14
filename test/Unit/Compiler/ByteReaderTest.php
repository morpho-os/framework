<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use Morpho\Compiler\ByteReader;
use Morpho\Compiler\IStringReader;
use Morpho\Compiler\StringReaderException;
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

        $this->checkState($reader, 4, 'test', ['test']);

        $input = 'a';
        $reader->setInput($input);
        $this->assertSame($input, $reader->input());
        $this->checkState($reader, 0, null, null);
        //$this->assertSame(0, $reader->charOffset());
        $this->assertTrue($reader->isLineStart());

        $reader->read('/a/');
        $reader->setInput('b');
        $this->checkState($reader, 0, null, null);
        //$this->assertSame(0, $reader->charOffset());
    }

    public function testConcat() {
        $reader = new ByteReader('a');

        $reader->read('/a/');

        $reader->concat('b');

        $this->checkState($reader, 1, 'a', ['a']);
        $this->assertFalse($reader->isEnd());
        $this->assertSame('b', $reader->read('/b/'));
        $this->assertTrue($reader->isEnd());

        $reader->concat('c');

        $this->checkState($reader, 2, 'b', ['b']);
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
        $this->checkState($reader, 4, 'stra', ['stra']);

        $this->assertSame(' ', $reader->read('/\s+/'));
        $this->checkState($reader, 5, ' ', [' ']);

        $this->assertSame('strb', $reader->read('/\w+/'));
        $this->checkState($reader, 9, 'strb', ['strb']);

        $this->assertSame(' ', $reader->read('/\s+/'));
        $this->checkState($reader, 10, ' ', [' ']);

        $this->assertSame('strc', $reader->read('/\w+/'));
        $this->checkState($reader, 14, 'strc', ['strc']);

        $this->assertNull($reader->read('/\w+/'));
        $this->checkState($reader, 14, null, null);

        $reader = new ByteReader('');

        $this->assertSame("", $reader->read('//'));
        $this->checkState($reader, 0, '', ['']);

        $this->assertNull($reader->read('/\w+/'));
        $this->checkState($reader, 0, null, null);
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

    public function testCheck() {
        $reader = new ByteReader("Foo Bar Baz");

        $this->assertSame("Foo", $reader->check('/Foo/'));
        $this->checkState($reader, 0, 'Foo', ['Foo']);

        $this->assertNull($reader->check('/Bar/'));
        $this->checkState($reader, 0, null, null);
    }

    public function testSkip() {
        $reader = new ByteReader('stra strb strc');

        $this->assertSame(4, $reader->skip('/\w+/'));
        $this->checkState($reader, 4, 'stra', ['stra']);

        $this->assertSame(1, $reader->skip('/\s+/'));
        $this->checkState($reader, 5, ' ', [' ']);

        $this->assertSame(4, $reader->skip('/\w+/'));
        $this->checkState($reader, 9, 'strb', ['strb']);

        $this->assertSame(1, $reader->skip('/\s+/'));

        $this->assertSame(4, $reader->skip('/\w+/'));
        $this->checkState($reader, 14, 'strc', ['strc']);

        $this->assertNull($reader->skip('/\w+/'));
        $this->checkState($reader, 14, null, null);

        $this->assertNull($reader->skip('/\s+/'));
        $this->checkState($reader, 14, null, null);

        $this->assertTrue($reader->isEnd());
        $this->checkState($reader, 14, null, null);
    }

    public function testSkip_EmptyStr() {
        $reader = new ByteReader("");

        $this->assertSame(0, $reader->skip('//'));
        $this->checkState($reader, 0, '', ['']);

        $this->assertSame(0, $reader->skip('//'));
        $this->checkState($reader, 0, '', ['']);
    }

    public function testSkip_MultiLineModifier() {
        $reader = new ByteReader("a\nbc");
        $this->assertSame(2, $reader->skip('/a\n/m'));
        $this->assertSame(1, $reader->skip('/^b/m'));
        $this->assertSame(1, $reader->skip('/c/'));
    }

    public function testLook() {
        $reader = new ByteReader("test string");

        $this->assertSame(4, $reader->look('/\w+/'));
        $this->checkState($reader, 0, 'test', ['test']);

        $this->assertSame(4, $reader->look('/\w+/'));
        $this->checkState($reader, 0, 'test', ['test']);

        $this->assertNull($reader->look('/\s+/'));
        $this->checkState($reader, 0, null, null);
    }

    public function testReadUntil() {
        $reader = new ByteReader("Foo Bar Baz");

        $this->assertSame("Foo", $reader->readUntil('/Foo/'));
        $this->checkState($reader, 3, 'Foo', ['Foo']);

        $reader = new ByteReader("Fri Dec 12 1975 14:39");

        $this->assertSame('Fri Dec 1', $reader->readUntil('/1/'));
        $this->checkState($reader, 9, '1', ['1']);

        $this->assertSame("Fri Dec ", $reader->preMatch());

        $this->assertNull($reader->readUntil('/XYZ/'));
        $this->checkState($reader, 9, null, null);
    }

    public function testReadUntil_Full() {
        $reader = new ByteReader("Foo Bar Baz");

        $this->assertSame(8, $reader->readUntil('/Bar /', false, false));
        $this->checkState($reader, 0, 'Bar ', ['Bar ']);

        $this->assertSame("Foo Bar ", $reader->readUntil('/Bar /', false, true));
        $this->checkState($reader, 0, 'Bar ', ['Bar ']);

        $this->assertSame(8, $reader->readUntil('/Bar /', true, false));
        $this->checkState($reader, 8, 'Bar ', ['Bar ']);

        $this->assertSame("Baz", $reader->readUntil('/az/', true, true));
        $this->checkState($reader, 11, 'az', ['az']);
    }

    public function testCheckUntil() {
        $reader = new ByteReader("Foo Bar Baz");
        $this->assertSame("Foo", $reader->checkUntil('/Foo/'));
        $this->checkState($reader, 0, 'Foo', ['Foo']);

        $this->assertSame("Foo Bar", $reader->checkUntil('/Bar/'));
        $this->checkState($reader, 0, 'Bar', ['Bar']);

        $this->assertNull($reader->checkUntil('/Qux/'));
        $this->checkState($reader, 0, null, null);
    }

    public function testSkipUntil() {
        $reader = new ByteReader("Foo Bar Baz");

        $this->assertSame(3, $reader->skipUntil('/Foo/'));
        $this->checkState($reader, 3, 'Foo', ['Foo']);

        $this->assertSame(4, $reader->skipUntil('/Bar/'));
        $this->checkState($reader, 7, 'Bar', ['Bar']);

        $this->assertNull($reader->skipUntil('/Qux/'));
        $this->checkState($reader, 7, null, null);
    }

    public function testLookUntil() {
        $reader = new ByteReader("test string");

        $this->assertSame(3, $reader->lookUntil('/s/'));
        $this->checkState($reader, 0, 's', ['s']);

        $reader->read('/test/');

        $this->assertSame(2, $reader->lookUntil('/s/'));
        $this->checkState($reader, 4, 's', ['s']);

        $this->assertNull($reader->lookUntil('/e/'));
        $this->checkState($reader, 4, null, null);
    }

    public function testReadByte() {
        $reader = new ByteReader('abcde');

        $this->assertSame('a', $reader->readByte());
        $this->checkState($reader, 1, 'a', ['a']);

        $this->assertSame('b', $reader->readByte());
        $this->checkState($reader, 2, 'b', ['b']);

        $this->assertSame('c', $reader->readByte());
        $this->checkState($reader, 3, 'c', ['c']);

        $this->assertSame('d', $reader->readByte());
        $this->checkState($reader, 4, 'd', ['d']);

        $this->assertSame('e', $reader->readByte());
        $this->checkState($reader, 5, 'e', ['e']);

        $this->assertNull($reader->readByte());
        $this->checkState($reader, 5, null, null);

        $reader = new ByteReader("\x00\x01");

        $this->assertSame("\x00", $reader->readByte());
        $this->checkState($reader, 1, "\x00", ["\x00"]);

        $this->assertSame("\x01", $reader->readByte());
        $this->checkState($reader, 2, "\x01", ["\x01"]);

        $reader = new ByteReader('');
        $this->assertNull($reader->readByte());
        $this->checkState($reader, 0, null, null);
    }

    public function testUnread() {
        $reader = new ByteReader('test string');

        $this->assertSame('test', $reader->read('/\w+/'));

        $reader->unread();

        $this->checkState($reader, 0, null, null);

        $this->assertSame("te", $reader->read('/../'));

        $this->assertNull($reader->read('/\d/'));

        try {
            $reader->unread();
            $this->fail();
        } catch (StringReaderException $e) {
            $this->assertSame("Previous match record doesn't exist", $e->getMessage());
        }
    }

    public function testPeek() {
        $reader = new ByteReader("test string");
        $this->assertSame('', $reader->peek(0));
        $this->checkState($reader, 0, null, null);

        $this->assertSame("test st", $reader->peek(7));
        $this->checkState($reader, 0, null, null);

        $this->assertSame("test st", $reader->peek(7));
        $this->checkState($reader, 0, null, null);

        $reader->read('/test/');

        $this->assertSame(" stri", $reader->peek(5));
        $this->checkState($reader, 4, 'test', ['test']);

        $this->assertSame(" string", $reader->peek(10));
        $this->checkState($reader, 4, 'test', ['test']);

        $reader->read('/ string/');

        $this->assertSame("", $reader->peek(10));
        $this->checkState($reader, 11, ' string', [' string']);
    }

    public function testTerminate() {
        $reader = new ByteReader('');

        $reader->terminate();

        $this->checkState($reader, 0, null, null);
        $this->assertTrue($reader->isEnd());

        $reader = new ByteReader('abcd');

        $reader->readByte();

        $reader->terminate();

        $this->checkState($reader, 4, null, null);
        $this->assertTrue($reader->isEnd());

        $reader->terminate();

        $this->checkState($reader, 4, null, null);
        $this->assertTrue($reader->isEnd());
    }

    public function testReset() {
        $reader = new ByteReader('abcd');

        $reader->readByte();

        $reader->reset();

        $this->checkState($reader, 0, null, null);

        $this->assertSame(null, $reader->matched());
        $this->assertSame(null, $reader->preMatch());
        $this->assertSame(null, $reader->postMatch());

        $reader->read('/\w+/');

        $reader->reset();

        $this->checkState($reader, 0, null, null);

        $reader->reset();

        $this->checkState($reader, 0, null, null);
    }

    public function testIsLineStart() {
        $reader = new ByteReader("a\nbbb\n\ncccc\nddd\r\neee");

        $this->assertTrue($reader->isLineStart());

        $reader->read('/a/');

        $this->assertFalse($reader->isLineStart());

        $reader->read('/\n/');

        $this->assertTrue($reader->isLineStart());

        $reader->read('/b/');

        $this->assertFalse($reader->isLineStart());

        $reader->read('/b/');

        $this->assertFalse($reader->isLineStart());

        $reader->read('/b/');

        $this->assertFalse($reader->isLineStart());

        $reader->read('/\n/');

        $this->assertTrue($reader->isLineStart());

        $reader->unread();

        $this->assertFalse($reader->isLineStart());

        $reader->read('/\n/');
        $reader->read('/\n/');

        $this->assertTrue($reader->isLineStart());

        $reader->read('/c+\n/');

        $this->assertTrue($reader->isLineStart());

        $reader->read('/d+\r\n/');

        $this->assertTrue($reader->isLineStart());

        $reader->read('/e+/');

        $this->assertFalse($reader->isLineStart());
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

    public function testMatchedSize() {
        $reader = new ByteReader('test string');

        $this->assertNull($reader->matchedSize());

        $reader->read('/test/');
        $this->assertSame(4, $reader->matchedSize());
        $this->assertSame(4, $reader->matchedSize());

        $reader->read('//');
        $this->assertSame(0, $reader->matchedSize());

        $reader->read('/x/');
        $this->assertNull($reader->matchedSize());
        $this->assertNull($reader->matchedSize());

        $reader->terminate();
        $this->assertNull($reader->matchedSize());

        $reader = new ByteReader('test string');
        $this->assertNull($reader->matchedSize());

        $reader->read('/test/');
        $this->assertSame(4, $reader->matchedSize());

        $reader->terminate();
        $this->assertNull($reader->matchedSize());
    }

    public function testSubgroups_Read() {
        $reader = new ByteReader("Timestamp: Fri Dec 12 1975 14:39");

        $reader->read("/Timestamp: /");

        $reader->read('/(\w+) (\w+) (\d+) /');

        $this->assertSame(['Fri Dec 12 ', "Fri", "Dec", "12"], $reader->subgroups());

        $reader->read('/(\w+) (\w+) (\d+) /');

        $this->assertNull($reader->subgroups());
    }

    public function testSubgroups_ReadUntil() {
        $reader = new ByteReader("Fri Dec 12 1975 14:39");

        $this->assertNull($reader->subgroups());

        $reader->readUntil('/ (\d+)\s+(\d+) /');

        $subgroups = $reader->subgroups();

        $this->assertSame(
            [
                ' 12 1975 ',
                '12',
                '1975',
            ],
            $subgroups
        );
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

    public function testPostMatch() {
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


    public function testRest() {
        $reader = new ByteReader('test string');
        $this->assertSame("test string", $reader->rest());
        $reader->read('/test/');
        $this->assertSame(" string", $reader->rest());
        $reader->read('/ string/');
        $this->assertSame("", $reader->rest());
        $reader->read('/ string/');
    }

    public function testRestSize() {
        $reader = new ByteReader('test string');

        $this->assertSame(11, $reader->restSize());

        $reader->read('/test/');

        $this->assertSame(7, $reader->restSize());

        $reader->read('/ string/');

        $this->assertSame(0, $reader->restSize());
    }

    public function testIsAnchored() {
        $reader = new ByteReader('', true);
        $this->assertTrue($reader->isAnchored());

        $reader = new ByteReader('', false);
        $this->assertFalse($reader->isAnchored());
    }

    private function checkState(ByteReader $reader, int $offset, ?string $matched, ?array $subgroups): void {
        $this->assertSame($offset, $reader->offset());
        $this->assertSame($matched, $reader->matched());
        $this->assertSame($subgroups, $reader->subgroups());
    }
}