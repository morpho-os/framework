<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use function Morpho\Base\{
    fromJson, partial, toJson, uniqueName, deleteDups, last, head, classify, escapeHtml, unescapeHtml, trimMore, init, sanitize, underscore, dasherize, camelize, humanize, titleize, htmlId, shorten, writeLn, normalizeEols, typeOf, prepend, append
};
use const Morpho\Base\{INT_TYPE, FLOAT_TYPE, BOOL_TYPE, STRING_TYPE, NULL_TYPE, ARRAY_TYPE, RESOURCE_TYPE};

class FunctionsTest extends TestCase {
    public function setUp() {
        //resetState();
    }

    public function tearDown() {
        if (isset($this->tmpHandle)) {
            fclose($this->tmpHandle);
        }
    }

    public function dataForTypeOf() {
        $filePath = tempnam($this->tmpDirPath(), __FUNCTION__);
        $this->tmpHandle = $fp = fopen($filePath, 'r');
        return [
            [
                INT_TYPE,
                5,
            ],
            [
                FLOAT_TYPE,
                3.14
            ],
            [
                BOOL_TYPE,
                true,
            ],
            [
                STRING_TYPE,
                "Hello",
            ],
            [
                NULL_TYPE,
                null,
            ],
            [
                ARRAY_TYPE,
                [],
            ],
            [
                RESOURCE_TYPE,
                $fp,
            ],
            [
                'Closure',
                function () {},
            ],
            [
                'stdClass',
                new \stdClass,
            ],
            [
                'ArrayObject',
                new \ArrayObject(),
            ],
            [
                'Morpho\\Base\\ArrayObject',
                new \Morpho\Base\ArrayObject,
            ],
        ];
    }

    /**
     * @dataProvider dataForTypeOf
     */
    public function testTypeOf($expected, $actual) {
        $this->assertEquals($expected, typeOf($actual));
    }

    public function testToAndFromJson() {
        $v = ['foo' => 'bar', 1 => new class {
            public $t = 123;
        }];
        $json = toJson($v);
        $this->assertInternalType('string', $json);
        $this->assertNotEmpty($json);
        $v1 = fromJson($json);
        $this->assertCount(count($v), $v1);
        $this->assertEquals($v['foo'], $v1['foo']);
        $this->assertEquals((array) $v[1], $v1[1]);
    }

    public function testNormalizeEols() {
        $this->assertEquals("foo\nbar\nbaz\n", normalizeEols("foo\r\nbar\rbaz\r\n"));
        $this->assertEquals("", normalizeEols(""));
    }

    public function testWriteLn_SingleArg() {
        ob_start();
        writeLn("Printed");
        $this->assertEquals("Printed\n", ob_get_clean());
    }

    public function testWriteLn_MultipleArgs() {
        ob_start();
        writeLn("bee", "ant");
        $this->assertEquals("bee\nant\n", ob_get_clean());
    }

    public function testWriteLn_ClosureGeneratorArg() {
        $gen = function () {
            foreach (['foo', 'bar', 'baz'] as $v) {
                yield $v;
            }
        };
        ob_start();
        writeLn($gen);
        $this->assertEquals("foo\nbar\nbaz\n", ob_get_clean());
    }

    public function testWriteLn_IterableArg() {
        $val = new \ArrayIterator(['foo', 'bar', 'baz']);
        ob_start();
        writeLn($val);
        $this->assertEquals("foo\nbar\nbaz\n", ob_get_clean());
    }

    public function testShorten() {
        $this->assertEquals('foo...', shorten('foobarb', 6));
        $this->assertEquals('foobar', shorten('foobar', 6));
        $this->assertEquals('fooba', shorten('fooba', 6));
        $this->assertEquals('foob', shorten('foob', 6));
        $this->assertEquals('foo', shorten('foo'), 6);
        $this->assertEquals('fo', shorten('fo'), 6);
        $this->assertEquals('f', shorten('f'), 6);
        $this->assertEquals('', shorten(''), 6);

        $this->assertEquals('foob!!', shorten('foobarb', 6, '!!'));
    }

    public function testUniqueName() {
        $this->assertEquals('unique0', uniqueName());
        $this->assertEquals('unique1', uniqueName());
    }

    public function testDeleteDups() {
        $this->assertEquals('12332', deleteDups(122332, 2));
        $this->assertEquals('aaa', deleteDups('aaa', 'b'));
        $this->assertEquals('a', deleteDups('aaa', 'a'));
    }

    public function testEscapeHtmlAndUnescapeHtml() {
        $original = '<h1>Hello</h1>';
        $text = escapeHtml($original);
        $this->assertEquals('&lt;h1&gt;Hello&lt;/h1&gt;', $text);
        $this->assertEquals($original, unescapeHtml($text));
    }

    public function testTrimMore() {
        $t = [
            '  ff  ',
            ' foo ' => [
                ' bar-',
            ],
        ];
        $expected = [
            'ff',
            ' foo ' => [
                'bar',
            ],
        ];
        $this->assertEquals($expected, trimMore($t, '-'));

        $this->assertEquals('ff', trimMore('__ ff  ', '_'));
    }

    public function testLast() {
        $this->assertEquals('StringTest', last('MorphoTest\\Base\\StringTest', '\\'));
        $this->assertEquals('', last('MorphoTest\\Base\\StringTest\\', '\\'));
        $this->assertEquals('Foo', last('Foo', '\\'));
        $this->assertEquals('', last('', '\\'));
    }

    public function testHead() {
        $this->assertEquals('MorphoTest', head('MorphoTest\\Base\\StringTest', '\\'));
        $this->assertEquals('', head('\\MorphoTest\\Base\\StringTest', '\\'));
        $this->assertEquals('Foo', head('Foo', '\\'));
        $this->assertEquals('', head('', '\\'));
    }

    public function testInit() {
        $this->assertEquals('Foo\\Bar', init('Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('\\Foo\\Bar', init('\\Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('Foo', init('Foo', '\\'));
        $this->assertEquals('', init('', '\\'));
    }

    public function testSanitize() {
        $input = "foo[\"1][b'ar]\x00`ls`;&quot;<>";
        $this->assertEquals('foo1barlsquot', sanitize($input, '_'));
    }

    public function testClassify() {
        $this->assertEquals('Foobar', classify('foobar'));
        $this->assertEquals('Foobar', classify("&\tf\no<>o\x00`bar"));
        $this->assertEquals('FooBar', classify('foo-bar'));
        $this->assertEquals('FooBar', classify('FooBar'));
        $this->assertEquals('FooBar', classify('foo_bar'));
        $this->assertEquals('FooBar', classify('-foo-bar-'));
        $this->assertEquals('FooBar', classify('_foo-bar_'));
        $this->assertEquals('FooBar', classify('_foo-bar_'));
        $this->assertEquals('FooBar', classify('_Foo_Bar_'));
        $this->assertEquals('FooBarBaz', classify('FooBar_Baz'));
        $this->assertEquals('FooBar', classify("  foo  bar  "));
        $this->assertEquals('Foo\\Bar\\Baz', classify('foo/bar/baz'));
        $this->assertEquals('Foo\\Bar\\Baz', classify('foo\\bar\\baz'));
        $this->assertEquals('\\Foo\\Bar\\BazTest', classify('foo\\bar/baz-test', true));
    }

    public function testUnderscore() {
        $this->assertCommon('underscore');
        $this->assertEquals('foo_bar', underscore('Foo Bar'));
        $this->assertEquals('foo_bar', underscore('FooBar'));
        $this->assertEquals('foo_bar', underscore('foo-bar'));
        $this->assertEquals('foo_bar', underscore('_foo_bar_'));
        $this->assertEquals('foo_bar', underscore('-foo_bar-'));
        $this->assertEquals('_foo_bar_', underscore('-foo_bar-', false));
        $this->assertEquals('foo_bar', underscore('-Foo_Bar-'));
        $this->assertEquals('foo_bar_baz', underscore('FooBar-Baz'));
        $this->assertEquals('foo_bar', underscore("  foo  bar  "));
    }

    public function testDasherize() {
        $this->assertCommon('dasherize');
        $this->assertEquals('foo-bar', dasherize('foo-bar'));
        $this->assertEquals('foo-bar', dasherize('FooBar'));
        $this->assertEquals('foo-bar', dasherize('foo_bar'));
        $this->assertEquals('foo-bar', dasherize('-foo-bar-'));
        $this->assertEquals('foo-bar', dasherize('_foo-bar_'));
        $this->assertEquals('-foo-bar-', dasherize('_foo-bar_', false));
        $this->assertEquals('foo-bar', dasherize('_Foo_Bar_'));
        $this->assertEquals('foo-bar-baz', dasherize('FooBar_Baz'));
        $this->assertEquals('foo-bar', dasherize("  foo  bar  "));
        $this->assertEquals('foo-bar', dasherize('fooBar'));
    }

    public function testCamelize() {
        $this->assertCommon('camelize');
        $this->assertEquals('fooBar', camelize('foo-bar'));
        $this->assertEquals('fooBar', camelize('FooBar'));
        $this->assertEquals('FooBar', camelize('FooBar', true));
        $this->assertEquals('fooBar', camelize('foo_bar'));
        $this->assertEquals('fooBar', camelize('-foo-bar-'));
        $this->assertEquals('fooBar', camelize('_foo-bar_'));
        $this->assertEquals('fooBar', camelize('_foo-bar_'));
        $this->assertEquals('fooBar', camelize('_Foo_Bar_'));
        $this->assertEquals('fooBarBaz', camelize('FooBar_Baz'));
        $this->assertEquals('fooBar', camelize("  foo  bar  "));
    }

    public function testHumanize() {
        $this->assertEquals('v&quot;&quot;v pe te Adam bob camel ized.', humanize('v""v pe_te Adam bob camelIzed.'));
        $this->assertEquals('v""v pe te Adam bob camel ized.', humanize('v""v pe_te Adam bob camelIzed.', false));
    }

    public function testTitleize() {
        $this->assertEquals('V&quot;&quot;v Pe Te Adam Bob Camel Ized.', titleize('v""v pe_te Adam bob camelIzed.'));
        $this->assertEquals('V""v pe te Adam bob camel ized.', titleize('v""v pe_te Adam bob camelIzed.', false, false));
    }

    public function testHtmlId() {
        $this->assertEquals('foo-1-bar-2-test', htmlId('foo[1][bar][2][test]'));
        $this->assertEquals('foo-1-bar-2-test-1', htmlId('foo_1-bar_2[test]'));
        $this->assertEquals('fo-o', htmlId('<fo>&o\\'));
        $this->assertEquals('fo-o-1', htmlId('<fo>&o\\'));
        $this->assertEquals('foo-bar', htmlId('FooBar'));
        $this->assertEquals('foo-bar-1', htmlId('FooBar'));
    }

    public function testPrepend() {
        $this->assertEquals(['prefixfoo', 'prefixbar', 'prefixbaz'], array_map(prepend('prefix'), ['foo', 'bar', 'baz']));
    }

    public function testAppend() {
        $this->assertEquals(['foosuffix', 'barsuffix', 'bazsuffix'], array_map(append('suffix'), ['foo', 'bar', 'baz']));
    }

    public function testPartial() {
        $add = function ($a, $b) {
            return $a + $b;
        };
        $add2 = partial($add, '2');
        $this->assertEquals(5, $add2(3));

        $concatenate = function ($a, $b, $c, $d, $e, $f) {
            return $a . $b . $c . $d . $e . $f;
        };
        $appendPrefix = partial($concatenate, 'foo', 'bar', 'baz');
        $this->assertEquals('foobarbazHelloWorld!', $appendPrefix('Hello', 'World', '!'));
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }

    protected function assertCommon($fn) {
        $fn = 'Morpho\Base\\' . $fn;
        $this->assertEquals('foobar', call_user_func($fn, 'foobar'));
        $this->assertEquals('foobar', call_user_func($fn, "&\tf\no<>o\x00`bar"));
    }
}
