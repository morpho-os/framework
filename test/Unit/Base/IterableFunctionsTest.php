<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use function Morpho\Base\{
    all, any, apply, append, chain, contains, filter, flatMap, head, init, last, map, prepend, reduce, tail, toArray
};
use Morpho\Testing\TestCase;

class IterableFunctionsTest extends TestCase {
    public function dataForEmptyList() {
        return [
            [
                [],
            ],
            [
                new \ArrayIterator([]),
            ],
        ];
    }

    // --------------------------------------------------------------------------------
    // Common tests for filter, flatMap, map

    public function dataForEmptyList_Common() {
        foreach (['filter', 'flatMap', 'map'] as $fn) {
            $fn = '\\Morpho\\Base\\' . $fn;
            foreach ($this->dataForEmptyList() as $row) {
                yield \array_merge([$fn], $row);
            }
        }
    }

    /** @dataProvider dataForEmptyList_Common */
    public function testEmptyList_Common(callable $fnToTest, $iter) {
        $this->checkResForEmptyList($fnToTest($this->errFn(), $iter), $iter);
    }

    // --------------------------------------------------------------------------------
    // all

    /** @dataProvider dataForEmptyList */
    public function testAll_EmptyList($list) {
        $this->assertTrue(all($this->errFn(), $list));
    }

    public function testAll_String() {
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

    public function testAny_String() {
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

    public function testAppend_String() {
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
    // apply

    /** @dataProvider dataForEmptyList */
    public function testApply_EmptyList($iter) {
        $fn = $this->errFn();
        $this->assertNull(apply($fn, $iter));
    }

    /* * @dataProvider dataForString */
    public function testApply_String() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForBytes */
    public function testApply_Bytes() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForArray_NumericKeys */
    public function testApply_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForArray_StringKeys */
    public function testApply_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForIterator_NumericKeys */
    public function testApply_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForIterator_StringKeys */
    public function testApply_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForGenerator_NumericKeys */
    public function testApply_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForGenerator_StringKeys */
    public function testApply_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // contains

    public function testContains_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testContains_String() {
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

    public function testFilter_String() {
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
            $this->assertTrue(\is_numeric($k));
            return $v !== 'c';
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals(['a', 'b', 'd'], \iterator_to_array($res, false));
    }

    public function testFilter_Iterator_StringKeys() {
        $it = new \ArrayIterator([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'c' => 'Uranus',
            'd' => 'Neptune',
        ]);
        $res = filter(function ($v, $k) {
            $this->assertTrue(\is_string($k));
            return $v !== 'Uranus';
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals([
            'a' => 'Mercury',
            'b'  => 'Jupiter',
            'd' => 'Neptune',
        ], \iterator_to_array($res));
    }

    public function testFilter_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testFilter_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // flatMap

    /* * @dataProvider dataFor_String */
    public function testFlatMap_String() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Bytes */
    public function testFlatMap_Bytes() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Array_NumericKeys */
    public function testFlatMap_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Array_StringKeys */
    public function testFlatMap_Array_StringKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Iterator_NumericKeys */
    public function testFlatMap_Iterator_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Iterator_StringKeys */
    public function testFlatMap_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Generator_NumericKeys */
    public function testFlatMap_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataFor_Generator_StringKeys */
    public function testFlatMap_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    // --------------------------------------------------------------------------------
    // head

    public function testHead_EmptyList() {
        $this->markTestIncomplete();
    }

    public function testHead_String_WithSeparator() {
        $this->assertEquals('Morpho', head(__NAMESPACE__ . '\\StringTest', '\\'));
        $this->assertEquals('', head('\\' . __NAMESPACE__ . '\\StringTest', '\\'));
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

    public function dataForInit_EmptyList() {
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

    /**
     * @dataProvider dataForInit_EmptyList
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
     * @dataProvider dataForInit_EmptyList
     */
    public function testLast_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        last($v, $sep);
    }

    public function testLast_String_WithSeparator() {
        $this->assertEquals('StringTest', last(__NAMESPACE__ . '\\StringTest', '\\'));
        $this->assertEquals('', last(__NAMESPACE__ . '\\StringTest\\', '\\'));
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

    public function testMap_String() {
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
            $this->assertTrue(\is_numeric($k));
            $map = [
                'a' => 'foo',
                'b' => 'bar',
                'c' => 'baz',
                'd' => 'pizza'
            ];
            return $map[$v];
        }, $it);
        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertEquals(['foo', 'bar', 'baz', 'pizza'], \iterator_to_array($res, false));
    }

    public function testMap_Iterator_StringKeys() {
        $this->markTestIncomplete();
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

    public function testPrepend_String() {
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

    public function testReduce_PreservingInitialValue() {
        $this->assertNull(reduce(function ($v) { return $v; }, ['foo', 'bar', 'baz']));
        $v = new \stdClass();
        $this->assertSame($v, reduce(function ($v) { return $v; }, ['foo', 'bar', 'baz'], $v));
    }

    /**
     * Taken from https://github.com/nikic/iter/blob/master/test/iterTest.php
     * @Copyright (c) 2013 by Nikita Popov.
     */
    public function testComplexReduce() {
        $this->assertSame('abcdef', reduce(function ($acc, $value, $key) {
            return $acc . $key . $value;
        }, ['a' => 'b', 'c' => 'd', 'e' => 'f'], ''));
    }

    /**
     * @dataProvider dataForEmptyList
     */
    public function testReduce_EmptyList($iter) {
        if (\is_string($iter)) {
            $this->markTestIncomplete();
        }
        $fn = $this->errFn();
        $init = 'abc';
        $this->assertSame($init, reduce($fn, $iter, $init));
        $this->assertNull(reduce($fn, $iter));
    }

    public function testReduce_String() {
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
     * @dataProvider dataForInit_EmptyList
     */
    public function testTail_EmptyList($v, $sep) {
        $this->expectEmptyListException();
        \iterator_to_array(tail($v, $sep));
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
        $arr = ['foo', 'bar', 'baz'];
        $tail = tail($arr);
        $this->assertSame(['foo', 'bar', 'baz'], $arr);
        $this->assertSame(['bar', 'baz'], $tail);
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
        $this->assertEquals(['b', 'c', 'd'], \iterator_to_array($res, false));
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

    // --------------------------------------------------------------------------------
    // toArray

    /** @dataProvider dataForEmptyList */
    public function testToArray_EmptyList($v) {
        $this->assertSame([], toArray($v));
    }

    /* * @dataProvider dataForString */
    public function testToArray_String() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForBytes */
    public function testToArray_Bytes() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForArray_NumericKeys */
    public function testToArray_Array_NumericKeys() {
        $this->markTestIncomplete();
    }

    public function testToArray_Array_StringKeys() {
        $arr = [
            'foo' => 'a',
            'bar' => 'b',
            'baz' => 'c'
        ];
        $iter = new \ArrayIterator($arr);
        $this->assertSame($arr, toArray($iter));
    }

    public function testToArray_Iterator_NumericKeys() {
        $arr = [
            'a',
            'b',
            'c'
        ];
        $iter = new \ArrayIterator($arr);
        $this->assertSame($arr, toArray($iter));
    }

    /* * @dataProvider dataForIterator_StringKeys */
    public function testToArray_Iterator_StringKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForGenerator_NumericKeys */
    public function testToArray_Generator_NumericKeys() {
        $this->markTestIncomplete();
    }

    /* * @dataProvider dataForGenerator_StringKeys */
    public function testToArray_Generator_StringKeys() {
        $this->markTestIncomplete();
    }

    ////////////////////////////////////////////////////////////////////////////////

    /**
     * Modified version from https://github.com/nikic/iter/blob/master/test/iterTest.php
     * @Copyright (c) 2013 by Nikita Popov.
     */
    public function testChain() {
        $chained = chain(\range(1, 3), \range(4, 6), \range(7, 9));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], toArray($chained));
        $this->assertSame([], toArray(chain()));
    }

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

    private function errFn(): \Closure {
        return function () {
            throw new \RuntimeException('This function must not be called');
        };
    }

    private function checkResForEmptyList($res, $iter) {
        if (\is_string($iter)) {
            $this->assertSame('', $res);
        } elseif (\is_array($iter)) {
            $this->assertSame([], $res);
        } else {
            $this->assertInstanceOf(\Generator::class, $res);
            $this->assertSame([], toArray($res));
        }
    }
}
