<?php declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Test\TestCase;
use function Morpho\Base\{
    endsWith, filter, hasPrefixFn, hasSuffixFn, notFn, suffixFn, fromJson, partialFn, composeFn, prefixFn, toJson, uniqueName, deleteDups, head, tail, init, last, classify, escapeHtml, unescapeHtml, trimMore, sanitize, underscore, dasherize, camelize, humanize, titleize, htmlId, shorten, showLn, normalizeEols, typeOf, wrapQ, startsWith, contains, formatBytes, map
};
use const Morpho\Base\{INT_TYPE, FLOAT_TYPE, BOOL_TYPE, STRING_TYPE, NULL_TYPE, ARRAY_TYPE, RESOURCE_TYPE};
use RuntimeException;

class FunctionsTest extends TestCase {
    private $tmpHandle;

    public function tearDown() {
        if (isset($this->tmpHandle)) {
            fclose($this->tmpHandle);
        }
    }

    public function dataForStartsWith() {
        return [
            [
                true, '', '',
            ],
            [
                false, '', 'foo',
            ],
            [
                true, 'foo', '',
            ],
            [
                true, 'foo', 'foo',
            ],
            [
                false, 'foo', 'foob',
            ],
            [
                true, 'foo', 'fo',
            ],
        ];
    }

    /**
     * @dataProvider dataForStartsWith
     */
    public function testStartsWith($expected, $s, $prefix) {
        $this->assertSame($expected, startsWith($s, $prefix));
    }

    public function dataForEndsWith() {
        return [
            [
                true, '', '',
            ],
            [
                true, 'abc', 'c',
            ],
            [
                true, 'abc', 'bc',
            ],
            [
                true, 'abc', 'abc',
            ],
            [
                false, 'abc', 'eabc',
            ],
            [
                false, '', 'abc',
            ],
        ];
    }

    /**
     * @dataProvider dataForEndsWith
     */
    public function testEndsWith($expected, $s, $suffix) {
        $this->assertSame($expected, endsWith($s, $suffix));
    }

    /**
     * @dataProvider dataForStartsWith
     */
    public function testHasPrefixFn($expected, $s, $prefix) {
        $fn = hasPrefixFn($prefix);
        $this->assertSame($expected, $fn($s));
    }

    /**
     * @dataProvider dataForEndsWith
     */
    public function testHasSuffixFn($expected, $s, $suffix) {
        $fn = hasSuffixFn($suffix);
        $this->assertSame($expected, $fn($s));
    }

    public function testPrepend_ArrayArg() {
        $this->markTestIncomplete();
    }

    public function testAppend_ArrayArg() {
        $this->markTestIncomplete();
    }

    public function testNotFn() {
        $fn = notFn(function (...$args) use (&$calledWithArgs) {
            $calledWithArgs = $args;
            return true;
        });
        $this->assertFalse($fn('foo', 'bar'));
        $this->assertEquals(['foo', 'bar'], $calledWithArgs);
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
                \ArrayObject::class,
                new \ArrayObject(),
            ],
            [
                \Morpho\Base\DateTime::class,
                new \Morpho\Base\DateTime,
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

    public function testFromJson_InvalidJsonThrowsException() {
        $this->expectException(RuntimeException::class, "Invalid JSON or too deep data");
        fromJson('S => {');
    }

    public function testNormalizeEols() {
        $this->assertEquals("foo\nbar\nbaz\n", normalizeEols("foo\r\nbar\rbaz\r\n"));
        $this->assertEquals("", normalizeEols(""));
    }

    public function testShowLn_NoArgsWritesSingleLine() {
        ob_start();
        showLn();
        $this->assertEquals("\n", ob_get_clean());
    }

    public function testShowLn_SingleArg() {
        ob_start();
        showLn("Printed");
        $this->assertEquals("Printed\n", ob_get_clean());
    }

    public function testShowLn_MultipleArgs() {
        ob_start();
        showLn("bee", "ant");
        $this->assertEquals("bee\nant\n", ob_get_clean());
    }

    public function testShowLn_ClosureGeneratorArg() {
        $gen = function () {
            foreach (['foo', 'bar', 'baz'] as $v) {
                yield $v;
            }
        };
        ob_start();
        showLn($gen);
        $this->assertEquals("foo\nbar\nbaz\n", ob_get_clean());
    }

    public function testShowLn_IterableArg() {
        $val = new \ArrayIterator(['foo', 'bar', 'baz']);
        ob_start();
        showLn($val);
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

    public function dataForEmptyList() {
        return [
            [
                [],
                null,
            ],
            [
                '',
                null,
            ],
            [
                '',
                '\\'
            ],
            [
                new \ArrayIterator([]),
                null,
            ],
        ];
    }

    // ------------------------------------------------------------------------
    // last()

    public function testLast_String_WithSeparator() {
        $this->assertEquals('StringTest', last('MorphoTest\Unit\\Base\\StringTest', '\\'));
        $this->assertEquals('', last('MorphoTest\Unit\\Base\\StringTest\\', '\\'));
        $this->assertEquals('Foo', last('Foo', '\\'));
    }

    public function testLast_String_WithoutSeparator() {
        $this->assertEquals(' ', last('   '));
        $this->assertEquals('4', last('01234'));
        $this->assertEquals('c', last('abc'));
    }

    public function testLast_Iterator_StringKeys() {
        $it = new \ArrayIterator(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
        $this->assertEquals('c', last($it));
    }

    /**
     * @dataProvider dataForEmptyList
     */
    public function testLast_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        last($v, $sep);
    }

    // ------------------------------------------------------------------------
    // head()

    /**
     * @dataProvider dataForEmptyList
     */
    public function testHead_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        head($v, $sep);
    }

    public function testHead_String_WithSeparator() {
        $this->assertEquals('MorphoTest', head('MorphoTest\Unit\\Base\\StringTest', '\\'));
        $this->assertEquals('', head('\\MorphoTest\Unit\\Base\\StringTest', '\\'));
        $this->assertEquals('Foo', head('Foo', '\\'));
    }

    public function testHead_String_WithoutSeparator() {
        $this->assertEquals(' ', head('   '));
        $this->assertEquals('0', head('01234'));
        $this->assertEquals('a', head('abc'));
    }

    public function testHead_Iterator_StringKeys() {
        $it = new \ArrayIterator(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
        $this->assertEquals('a', head($it));
    }

    // ------------------------------------------------------------------------

    public function dataForHeadAndLast_Array() {
        $lastFn = 'Morpho\\Base\\last';
        $headFn = 'Morpho\\Base\\head';
        $numericKeysScalarEls = ['a', 'b', 'c'];
        $nonNumericKeysScalarEls = ['foo' => 'a', 'bar' => 'b', 'baz' => 'c'];
        $numericKeysArrayEls = [['foo' => 'a'], ['bar' => 'b'], ['baz' => 'c']];
        $nonNumericKeysArrayEls = ['Jupiter' => ['foo' => 'a'], 'Saturn' => ['bar' => 'b'], 'Uranus' => ['baz' => 'c']];
        return [
            // Numeric keys scalars
            [
                'c',
                $numericKeysScalarEls,
                $lastFn,
            ],
            [
                'a',
                $numericKeysScalarEls,
                $headFn,
            ],

            // Non-numeric keys scalars
            [
                'c',
                $nonNumericKeysScalarEls,
                $lastFn,
            ],
            [
                'a',
                $nonNumericKeysScalarEls,
                $headFn,
            ],

            // Numeric keys arrays
            [
                ['baz' => 'c'],
                $numericKeysArrayEls,
                $lastFn,
            ],
            [
                ['foo' => 'a'],
                $numericKeysArrayEls,
                $headFn,
            ],

            // Non-numeric keys arrays
            [
                ['baz' => 'c'],
                $nonNumericKeysArrayEls,
                $lastFn,
            ],
            [
                ['foo' => 'a'],
                $nonNumericKeysArrayEls,
                $headFn,
            ],
        ];
    }

    /**
     * @dataProvider dataForHeadAndLast_Array
     */
    public function testHeadAndLast_Array($expected, array $arr, callable $fn) {
        $copy = $arr;
        $this->assertEquals($expected, $fn($arr));
        $this->assertEquals($copy, $arr);
    }

    // ------------------------------------------------------------------------
    // init()

    public function testInit_String_WithSeparator() {
        $this->assertEquals('Foo\\Bar', init('Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('\\Foo\\Bar', init('\\Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('', init('Foo', '\\'));
    }

    public function testInit_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider dataForEmptyList
     */
    public function testInit_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        init($v, $sep);
    }

    // ------------------------------------------------------------------------
    // tail()

    public function testTail_String_WithSeparator() {
        $this->assertEquals('Bar\\Baz', tail('Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('Bar\\Baz\\', tail('Foo\\Bar\\Baz\\', '\\'));
        $this->assertEquals('', tail('Foo', '\\'));
    }

    public function testTail_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    /**
     * @dataProvider dataForEmptyList
     */
    public function testTail_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        iterator_to_array(tail($v, $sep));
    }

    public function testTail_Iterator() {
        $it = new \ArrayIterator([
            'a',
            'b',
            'c',
            'd'
        ]);
        $res = tail($it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals(['b', 'c', 'd'], iterator_to_array($res, false));
    }

    // ------------------------------------------------------------------------

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
        $this->assertEquals('foo__bar', underscore('foo__bar'));
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
        $this->assertEquals('foo--bar', dasherize('foo--bar'));
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

    public function testPrefixFn() {
        $this->assertEquals(['prefixfoo', 'prefixbar', 'prefixbaz'], array_map(prefixFn('prefix'), ['foo', 'bar', 'baz']));
    }

    public function testSuffixFn() {
        $this->assertEquals(['foosuffix', 'barsuffix', 'bazsuffix'], array_map(suffixFn('suffix'), ['foo', 'bar', 'baz']));
    }

    public function testPartialFn() {
        $add = function ($a, $b) {
            return $a + $b;
        };
        $add2 = partialFn($add, '2');
        $this->assertEquals(5, $add2(3));

        $concatenate = function ($a, $b, $c, $d, $e, $f) {
            return $a . $b . $c . $d . $e . $f;
        };
        $appendPrefix = partialFn($concatenate, 'foo', 'bar', 'baz');
        $this->assertEquals('foobarbazHelloWorld!', $appendPrefix('Hello', 'World', '!'));
    }

    public function testComposeFn() {
        $g = function ($a) {
            return 'g' . $a;
        };
        $f = function ($b) {
            return 'f' . $b;
        };
        $this->assertEquals('fghello', composeFn($f, $g)('hello'));
    }

    public function testRequireFile() {
        $this->markTestIncomplete();
    }

    public function dataForWrapQ() {
        return [
            ["'Hello'", 'Hello'],
            [
                [
                    "'foo'",
                    "'bar'",
                ],
                [
                    'foo',
                    'bar'
                ],
            ],
            [
                [
                    'a' => "'foo'",
                    'b' => "'bar'",
                ],
                [
                    'a' => 'foo',
                    'b' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForWrapQ
     */
    public function testWrapQ($expected, $actual) {
        $this->assertSame($expected, wrapQ($actual));
    }

    public function testContains_Unicode() {
        $haystack = 'ℚ ⊂ ℝ ⊂ ℂ';
        $this->assertTrue(contains($haystack, ''));
        $this->assertTrue(contains($haystack, $haystack));
        $this->assertTrue(contains($haystack, 'ℚ ⊂'));
        $this->assertTrue(contains($haystack, '⊂ ℂ'));
        $this->assertTrue(contains($haystack, '⊂ ℝ ⊂'));
        $this->assertFalse(contains($haystack, 'abc'));
    }

    public function testFormatBytes() {
        $bytes = "\x2f\xe0\xab\x00\x01\xe0";
        $this->assertEquals('\x2f\xe0\xab\x00\x01\xe0', formatBytes($bytes));
        $this->assertEquals('\x2F\xE0\xAB\x00\x01\xE0', formatBytes($bytes, '\x%02X'));
    }

    // ------------------------------------------------------------------------
    // filter()

    public function testFilter_Iterator_NumericKeys() {
        $it = new \ArrayIterator([
            'a',
            'b',
            'c',
            'd'
        ]);
        $res = filter(function ($v, $k) {
            $this->assertTrue(is_numeric($k));
            return $v !== 'c';
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals(['a', 'b', 'd'], iterator_to_array($res, false));
    }

    public function testFilter_Iterator_StringKeys() {
        $it = new \ArrayIterator([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'c' => 'Uranus',
            'd' => 'Neptune',
        ]);
        $res = filter(function ($v, $k) {
            $this->assertTrue(is_string($k));
            return $v !== 'Uranus';
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'd' => 'Neptune',
        ], iterator_to_array($res));
    }

    public function testFilter_String() {
        $this->markTestIncomplete();
    }

    public function testFilter_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testFilter_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    // ------------------------------------------------------------------------
    // map

    public function testMap_Iterator_NumericKeys() {
        $it = new \ArrayIterator([
            'a',
            'b',
            'c',
            'd'
        ]);
        $res = map(function ($v, $k) {
            $this->assertTrue(is_numeric($k));
            $map = [
                'a' => 'foo',
                'b' => 'bar',
                'c' => 'baz',
                'd' => 'pizza'
            ];
            return $map[$v];
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals(['foo', 'bar', 'baz', 'pizza'], iterator_to_array($res, false));
    }
/*
    public function testMap_Iterator_StringKeys() {
        $it = new \ArrayIterator([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'c' => 'Uranus',
            'd' => 'Neptune',
        ]);
        $res = filter(function ($v, $k) {
            $this->assertTrue(is_string($k));
            return $v !== 'Uranus';
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'd' => 'Neptune',
        ], iterator_to_array($res));
    }

    public function testMap_String() {
        $this->markTestIncomplete();
    }

    public function testMap_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testMap_Array_NumericKeys() {
        $this->markTestIncomplete();
    }
*/
    private function assertCommon($fn) {
        $fn = 'Morpho\Base\\' . $fn;
        $this->assertEquals('foobar', call_user_func($fn, 'foobar'));
        $this->assertEquals('foobar', call_user_func($fn, "&\tf\no<>o\x00`bar"));
    }

    private function expectEmptyListException() {
        $this->expectException(RuntimeException::class, 'Empty list');
    }
}
