<?php
namespace MorphoTest\Base;

use Morpho\Base\InvalidOptionsException;
use Morpho\Test\TestCase;
use Morpho\Base\ArrayTool;

class ArrayToolTest extends TestCase {
    public function dataForIsSubset() {
        return [
            [
                true, [], [],
            ],
            [
                false, [], ['a', 'b']
            ],
            [
                true, ['a', 'b'], [],
            ],
            [
                true, ['a', 'b'], ['a'],
            ],
            [
                true, ['a', 'b'], ['a', 'b'],
            ],
            [
                false, ['a', 'b'], ['a', 'b', 'c'],
            ],
            [
                false, [2 => 'a'], [3 => 'a'],
            ],
            [
                true, [3 => 'a'], [3 => 'a'],
            ],
            [
                true, ['foo' => 'a'], [],
            ],
            [
                true, ['foo' => 'a', 'bar' => 'b'], ['foo' => 'a'],
            ],
            [
                true, ['foo' => 'a', 'bar' => 'b'], ['foo' => 'a', 'bar' => 'b'],
            ],
            [
                true, ['foo' => 'a', 'bar' => 'b', 'pizza'], ['foo' => 'a', 'bar' => 'b'],
            ],
            [
                true, ['foo' => 'a', 'bar' => 'b', 'pizza'], ['pizza'],
            ],
            [
                false, ['foo' => 'a', 'bar' => 'b', 'pizza'], ['pizza', 'bar' => 'foo'],
            ],
        ];
    }

    /**
     * @dataProvider dataForIsSubset
     */
    public function testIsSubset($expected, $a, $b) {
        $this->assertSame($expected, ArrayTool::isSubset($a, $b));
    }

    public function testSubsets() {
        $this->assertEquals([[]], ArrayTool::subsets([]));
        $this->assertEquals([[], [1]], ArrayTool::subsets([1]));
        $check = function ($expected, $actual) {
            $this->assertCount(count($expected), $actual);
            foreach ($expected as $val) {
                $this->assertContains($val, $actual);
            }
        };
        $check(
            [
                [],
                ['a'],
                ['b'],
                ['c'],
                ['a', 'b'],
                ['b', 'c'],
                ['a', 'c'],
                ['a', 'b', 'c']
            ],
            ArrayTool::subsets(['a', 'b', 'c'])
        );
    }

    public function dataForSetsEqual() {
        return [
            [
                [],
                [],
                true,
            ],
            [
                [],
                [0],
                false,
            ],
            [
                ['a', 'b', 'c'],
                [97, 98, 99],
                false,
            ],
            [
                ['1'],
                [1],
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataForSetsEqual
     */
    public function testSetsEqual($a, $b, $expected) {
        $this->assertEquals($expected, ArrayTool::setsEqual($a, $b));
    }
    
    public function testUnset_Cases() {
        $this->assertEquals([], ArrayTool::unset([], 'some'));
        $this->assertEquals([], ArrayTool::unset([], null));
    }

    public function testUnset_StringKeys() {
        $this->assertEquals(['one' => 'first-val'], ArrayTool::unset(['one' => 'first-val', 'two' => 'second-val'], 'second-val'));
    }

    public function testUnset_IntKeys() {
        $obj1 = new \stdClass();
        $obj2 = new \stdClass();
        $this->assertEquals([$obj2], array_values(ArrayTool::unset([$obj1, $obj2], $obj1)));

        $this->assertEquals(['one', 'two'], array_values(ArrayTool::unset(['one', 'two'], 'some')));

        $this->assertEquals(['one'], array_values(ArrayTool::unset(['one', 'two'], 'two')));
    }

    public function dataForInit() {
        return [
            [
                ['a', 'b', 'c', 'd'],
                ['a', 'b', 'c'],
            ],
            [
                ['foo'],
                [],
            ],
            [
                ['foo' => 5, 'bar' => 6, 'baz' => 7],
                ['foo' => 5, 'bar' => 6],
            ],
        ];
    }

    /**
     * @dataProvider dataForInit
     */
    public function testInit($val, $expected) {
        $valOriginal = $val;
        $this->assertSame($expected, ArrayTool::init($val));
        $this->assertSame($valOriginal, $val);
    }

    public function testInitThrowsExceptionForEmptyArray() {
        $this->expectException(\UnexpectedValueException::class, "Empty list");
        ArrayTool::init([]);
    }

    public function dataForLast() {
        return [
            [
                ['a', 'b', 'c', 'd'],
                'd',
            ],
            [
                ['foo'],
                'foo',
            ],
            [
                ['foo' => 5, 'bar' => 6, 'baz' => 7],
                7,
            ],
        ];
    }

    /**
     * @dataProvider dataForLast
     */
    public function testLast($val, $expected) {
        $valOriginal = $val;
        $this->assertSame($expected, ArrayTool::last($val));
        $this->assertSame($valOriginal, $val);
    }

    public function testLastThrowsExceptionForEmptyArray() {
        $this->expectException(\UnexpectedValueException::class, "Empty list");
        ArrayTool::last([]);
    }

    public function dataForTail() {
        return [
            [
                ['a', 'b', 'c', 'd'],
                ['b', 'c', 'd'],
            ],
            [
                ['foo'],
                [],
            ],
            [
                ['foo' => 5, 'bar' => 6, 'baz' => 7],
                ['bar' => 6, 'baz' => 7],
            ],
        ];
    }

    /**
     * @dataProvider dataForTail
     */
    public function testTail($val, $expected) {
        $valOriginal = $val;
        $this->assertSame($expected, ArrayTool::tail($val));
        $this->assertSame($valOriginal, $val);
    }

    public function testTailThrowsExceptionForEmptyArray() {
        $this->expectException(\UnexpectedValueException::class, "Empty list");
        ArrayTool::tail([]);
    }

    public function dataForHead() {
        return [
            [
                ['a', 'b', 'c', 'd'],
                'a',
            ],
            [
                ['foo'],
                'foo',
            ],
            [
                ['foo' => 5, 'bar' => 6, 'baz' => 7],
                5,
            ],
        ];
    }

    /**
     * @dataProvider dataForHead
     */
    public function testHead($val, $expected) {
        $valOriginal = $val;
        $this->assertSame($expected, ArrayTool::head($val));
        $this->assertSame($valOriginal, $val);
    }

    public function testHeadThrowsExceptionForEmptyArray() {
        $this->expectException(\UnexpectedValueException::class, "Empty list");
        ArrayTool::head([]);
    }

    public function testToKeyed() {
        $this->assertEquals(
            [
                ':-)' => [
                    'one' => ':)', 'two' => ':-)', 'three' => ':+)',
                ],
                ':-]' => [
                    'one' => ':]', 'two' => ':-]', 'three' => ':+]',
                ],
            ],
            ArrayTool::toKeyed(
                [
                    [
                        'one' => ':)', 'two' => ':-)', 'three' => ':+)',
                    ],
                    [
                        'one' => ':]', 'two' => ':-]', 'three' => ':+]',
                    ],
                ],
                'two'
            )
        );
    }

    public function testToKeyed_WithDropValueOption() {
        $this->assertEquals(
            [
                ':-)' => [
                    'one' => ':)', 'three' => ':+)',
                ],
                ':-]' => [
                    'one' => ':]', 'three' => ':+]',
                ],
            ],
            ArrayTool::toKeyed(
                [
                    [
                        'one' => ':)', 'two' => ':-)', 'three' => ':+)',
                    ],
                    [
                        'one' => ':]', 'two' => ':-]', 'three' => ':+]',
                    ],
                ],
                'two',
                true
            )
        );
    }

    public function dataForHandleOptions() {
        return [
            [
                ['foo' => 'my-default'],
                [],
                ['foo' => 'my-default'],
            ],
            [
                ['foo' => 'my-option'],
                ['foo' => 'my-option'],
                ['foo' => 'my-default'],
            ],
            [
                ['foo' => 'my-option'],
                ['foo' => 'my-option'],
                ['foo' => 'my-default'],
            ],
        ];
    }

    /**
     * @dataProvider dataForHandleOptions
     */
    public function testHandleOptions($expected, $options, $defaultOptions) {
        $this->assertEquals(
            $expected,
            ArrayTool::handleOptions(
                $options,
                $defaultOptions
            )
        );
    }

    public function testHandleOptions_ThrowsExceptionWhenDefaultOptionsAreMissing() {
        $this->expectException(InvalidOptionsException::class, "Invalid options: foo");
        ArrayTool::handleOptions(['foo' => 'bar'], ['one' => 1]);
    }

    public function testHandleOptions_InvalidOptionsNumericKeys() {
        $this->expectException(InvalidOptionsException::class, "Invalid options: 2, 5");
        ArrayTool::handleOPtions([2 => 'two', 'foo' => 'bar', 5 => 'five'], ['foo' => 'baz']);
    }

    public function testUnsetRecursive() {
        $array = $this->_testArray();
        $expected = [
            'foo' => 'test',
            'bar' => [
                'something',
            ],
            'baz' => [
                'test' => [],
            ],
        ];
        $this->assertEquals($expected, ArrayTool::unsetRecursive($array, 'unsetMe'));
        $this->assertEquals($expected, $array);
    }

    public function testCamelizeKeys() {
        $array = [
            'foo-bar' => 'one',
            'bar_baz' => 'two',
        ];
        $expected = [
            'fooBar' => 'one',
            'barBaz' => 'two',
        ];
        $this->assertEquals($expected, ArrayTool::camelizeKeys($array));
    }

    public function testUnderscoreKeys() {
        $array = [
            'fooBar' => 'one',
            'barBaz' => 'two',
        ];
        $expected = [
            'foo_bar' => 'one',
            'bar_baz' => 'two',
        ];
        $this->assertEquals($expected, ArrayTool::underscoreKeys($array));
    }

    public function testHash() {
        $array = $this->_testArray();
        $hash1 = ArrayTool::hash($array);
        $hash2 = ArrayTool::hash($array);
        $this->assertTrue(!empty($hash1) && !empty($hash2));
        $this->assertEquals($hash1, $hash2);

        $array['other'] = 'item';
        $hash3 = ArrayTool::hash($array);
        $this->assertTrue(!empty($hash3));
        $this->assertNotEquals($hash1, $hash3);
    }

    public function testUnion() {
        // {numeric keys, string keys, mixed keys}
        $this->assertEquals(['foo' => 'kiwi'], ArrayTool::union(['foo' => 'apple'], ['foo' => 'kiwi']));
        $this->markTestIncomplete();
    }

    public function dataForSymmetricDiff() {
        // for each {numeric keys, string keys, mixed keys}
        // check {value !=, key !=}
        return [
            [
                ['foo'],
                ['foo'],
                [],
            ],
            [
                ['foo'],
                ['foo'],
                [],
            ],
            [
                [],
                [],
                [],
            ],
            // Numeric keys
            [
                // Numeric keys: keys ==, values !=
                ['foo', 'bar', 'baz'],
                ['foo', 'bar'],
                ['baz'],
            ],
            [
                // Numeric sequential keys: keys ==, values ==
                ['banana', 'kiwi', 'cherry'],
                ['pear', 'banana', 'mango'],
                ['pear', 'mango', 'kiwi', 'cherry'],
            ],
            [
                // Numeric keys: keys !=, values ==
                ['foo'],
                [1 => 'foo', 0 => 'bar'],
                [3 => 'bar'],
            ],
            [
                // Numeric keys: keys !=, values !=
                ['pear', 'banana', 'mango', 'kiwi', 'cherry'],
                [7 => 'pear', 11 => 'banana', 24 => 'mango'],
                [6 => 'kiwi', 0 => 'cherry'],
            ],

            // String keys
            [
                // String keys: keys !=, values !=
                ['foo' => 'banana', 'bar' => 'kiwi', 'baz' => 'cherry'],
                ['foo' => 'banana'],
                ['bar' => 'kiwi', 'baz' => 'cherry'],
            ],
            [
                // String keys: keys !=, values ==
                [],
                ['k1' => 'v1'],
                ['k2' => 'v1'],
            ],
            [
                // String keys: keys ==, values !=
                ['k1' => 'v2'],
                ['k1' => 'v1'],
                ['k1' => 'v2'],
            ],
            [
                // String keys: keys ==, values ==
                [],
                ['k1' => 'v2'],
                ['k1' => 'v2'],
            ],
        ];
    }

    /**
     * @dataProvider dataForSymmetricDiff
     */
    public function testSymmetricDiff(array $expected, array $a, array $b) {
        $this->assertEquals($expected, ArrayTool::symmetricDiff($a, $b));
    }

    protected function _testArray() {
        return [
            'foo'     => 'test',
            'bar'     => [
                'something',
            ],
            'unsetMe' => 1,
            'baz'     => [
                'test' => [
                    'unsetMe' => [
                        'unsetMe' => 'test',
                    ],
                ],
            ],
        ];
    }
}
