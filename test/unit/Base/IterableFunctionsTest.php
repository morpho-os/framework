<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Base;

use function Morpho\Base\{all, any, append, contains, filter, head, init, last, map, prepend, reduce, tail};
use Morpho\Test\TestCase;

class IterableFunctionsTest extends TestCase {
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

    // --------------------------------------------------------------------------------
    // all

    public function testAll_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testAll_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testAll_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testAll_Bytes() {
        $this->markTestIncomplete();
    }

    public function testAll_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAll_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAll_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAll_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAll_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAll_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // any

    public function testAny_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testAny_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testAny_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testAny_Bytes() {
        $this->markTestIncomplete();
    }

    public function testAny_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAny_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAny_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAny_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAny_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAny_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // append

    public function testAppend_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testAppend_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testAppend_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testAppend_Bytes() {
        $this->markTestIncomplete();
    }

    public function testAppend_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAppend_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAppend_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAppend_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testAppend_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testAppend_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // contains

    public function testContains_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testContains_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testContains_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testContains_Bytes() {
        $this->markTestIncomplete();
    }

    public function testContains_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testContains_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testContains_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testContains_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testContains_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testContains_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // filter

    public function testFilter_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testFilter_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testFilter_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testFilter_Bytes() {
        $this->markTestIncomplete();
    }

    public function testFilter_Array_NumericKeys() {
        $res = filter(function ($v, $_) {
            return $v !== 'fruit';
        }, ['fruit', 3, 'fruit', 'planet']);
        $this->assertSame([3, 'planet'], $res);
    }

    public function testFilter_Array_StringKeys() {
        $res = filter(function ($v, $k) {
            return $k !== 'apple' && $v !== 3;
        }, ['orange' => 'fruit', 'three' => 3, 'apple' => 'fruit', 'earth' => 'planet']);
        $this->assertSame(['orange' => 'fruit', 'earth' => 'planet'], $res);
    }

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

    public function testFilter_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testFilter_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // head

    public function testHead_EmptyList() {
        $this->markTestIncomplete();
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

    public function testHead_Bytes() {
        $this->markTestIncomplete();
    }

    public function testHead_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testHead_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testHead_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testHead_Iterator_StringKeys() {
        $it = new \ArrayIterator(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
        $this->assertEquals('a', head($it));
    }

    public function testHead_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testHead_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // init

    /**
     * @dataProvider dataForEmptyList
     */
    public function testInit_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        init($v, $sep);
    }

    public function testInit_String_WithSeparator() {
        $this->assertEquals('Foo\\Bar', init('Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('\\Foo\\Bar', init('\\Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('', init('Foo', '\\'));
    }

    public function testInit_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testInit_Bytes() {
        $this->markTestIncomplete();
    }

    public function testInit_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testInit_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testInit_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testInit_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testInit_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testInit_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // last

    /**
     * @dataProvider dataForEmptyList
     */
    public function testLast_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        last($v, $sep);
    }

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

    public function testLast_Bytes() {
        $this->markTestIncomplete();
    }

    public function testLast_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testLast_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testLast_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testLast_Iterator_StringKeys() {
        $it = new \ArrayIterator(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
        $this->assertEquals('c', last($it));
    }

    public function testLast_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testLast_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // map

    public function testMap_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testMap_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testMap_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testMap_Bytes() {
        $this->markTestIncomplete();
    }

    public function testMap_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testMap_Array_StringKeys() {
        $this->markTestIncomplete();
    }

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

    public function testMap_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testMap_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // prepend

    public function testPrepend_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testPrepend_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testPrepend_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Bytes() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testPrepend_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // reduce

    public function testReduce_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testReduce_String_WithSeparator() {
        $this->markTestIncomplete();
    }

    public function testReduce_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testReduce_Bytes() {
        $this->markTestIncomplete();
    }

    public function testReduce_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testReduce_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testReduce_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testReduce_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testReduce_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testReduce_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // tail

    /**
     * @dataProvider dataForEmptyList
     */
    public function testTail_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        iterator_to_array(tail($v, $sep));
    }

    public function testTail_String_WithSeparator() {
        $this->assertEquals('Bar\\Baz', tail('Foo\\Bar\\Baz', '\\'));
        $this->assertEquals('Bar\\Baz\\', tail('Foo\\Bar\\Baz\\', '\\'));
        $this->assertEquals('', tail('Foo', '\\'));
    }

    public function testTail_String_WithoutSeparator() {
        $this->markTestIncomplete();
    }

    public function testTail_Bytes() {
        $this->markTestIncomplete();
    }

    public function testTail_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testTail_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testTail_Iterator_NumericKeys() {
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

    public function testTail_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    public function testTail_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testTail_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    ////////////////////////////////////////////////////////////////////////////////

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

    public function testContains_Unicode() {
        $haystack = 'ℚ ⊂ ℝ ⊂ ℂ';
        $this->assertTrue(contains($haystack, ''));
        $this->assertTrue(contains($haystack, $haystack));
        $this->assertTrue(contains($haystack, 'ℚ ⊂'));
        $this->assertTrue(contains($haystack, '⊂ ℂ'));
        $this->assertTrue(contains($haystack, '⊂ ℝ ⊂'));
        $this->assertFalse(contains($haystack, 'abc'));
    }

    private function expectEmptyListException() {
        $this->expectException(\RuntimeException::class, 'Empty list');
    }
}