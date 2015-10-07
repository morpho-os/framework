<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\ArrayTool;

class ArrayToolTest extends TestCase {
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
        $this->setExpectedException("\\UnexpectedValueException", "Empty list");
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
                7
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
        $this->setExpectedException("\\UnexpectedValueException", "Empty list");
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
                []
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
        $this->setExpectedException("\\UnexpectedValueException", "Empty list");
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
                'foo'
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
        $this->setExpectedException("\\UnexpectedValueException", "Empty list");
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

    public function dataForEnsureHasRequiredItems_Invalid() {
        return [
            [
                ['foo' => 1, 'baz' => 2],
                ['foo', 'bar'],
            ],
            [
                ['foo' => 1],
                ['foo', 'bar'],
            ],
            [
                [],
                ['foo', 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider dataForEnsureHasRequiredItems_Invalid
     */
    public function testEnsureHasRequiredItems_Invalid($actual, $requiredKeys) {
        $this->setExpectedException('\RuntimeException', 'Required items are missing');
        ArrayTool::ensureHasRequiredItems($actual, $requiredKeys);
    }

    public function dataForEnsureHasRequiredItems_Valid() {
        return [
            [
                ['foo' => 1, 'bar' => 2],
                ['foo', 'bar'],
            ],
            [
                ['foo' => 1, 'bar' => 2, 'baz' => 3],
                ['foo', 'bar'],
            ],
            [
                [],
                [],
            ]
        ];
    }

    /**
     * @dataProvider dataForEnsureHasRequiredItems_Valid
     */
    public function testEnsureHasRequiredItems_Valid($actual, $requiredKeys) {
        ArrayTool::ensureHasRequiredItems($actual, $requiredKeys);
    }

    public function dataForEnsureHasOnlyKeys_Invalid() {
        return [
            [
                ['foo' => '1', 'something' => 2],
                ['foo', 'bar', 'baz'],
                ['something'],
            ],
            [
                ['foo' => '2', 'bar' => 2, 'baz' => 3, 'something' => 4],
                ['foo', 'bar', 'baz'],
                ['something'],
            ],
        ];
    }

    /**
     * @dataProvider dataForEnsureHasOnlyKeys_Invalid
     */
    public function testCheckAllowed_Invalid($actual, $allowedKeys, $notAllowedItems) {
        $this->setExpectedException('\RuntimeException', 'Not allowed items are present: ' . implode(', ', $notAllowedItems));
        ArrayTool::ensureHasOnlyKeys($actual, $allowedKeys);
    }

    public function dataForEnsureHasOnlyKeys_Valid() {
        return [
            [
                ['foo' => '1', 'bar' => 2, 'baz' => 3],
                ['foo', 'bar', 'baz'],
            ],
            [
                ['foo' => 1],
                ['foo', 'bar'],
            ],
            [
                [],
                ['foo', 'bar'],
            ],
            [
                [],
                [],
            ]
        ];
    }

    /**
     * @dataProvider dataForEnsureHasOnlyKeys_Valid
     */
    public function testEnsureHasOnlyKeys_Valid($actual, $allowedKeys) {
        ArrayTool::ensureHasOnlyKeys($actual, $allowedKeys);
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

    public function testHandleOptionsThrowsExceptionWhenDefaultOptionsAreMissing() {
        $this->setExpectedException('\RuntimeException', "Not allowed items are present: foo");
        ArrayTool::handleOptions(['foo' => 'bar'], ['one' => 1]);
    }

    public function testUnsetRecursive() {
        $array = $this->getTestArray();
        $expected = array(
            'foo' => 'test',
            'bar' => array(
                'something',
            ),
            'baz' => array(
                'test' => array(),
            ),
        );
        $this->assertEquals($expected, ArrayTool::unsetRecursive($array, 'unsetMe'));
        $this->assertEquals($expected, $array);
    }

    public function testCamelizeKeys() {
        $array = array(
            'foo-bar' => 'one',
            'bar_baz' => 'two',
        );
        $expected = array(
            'fooBar' => 'one',
            'barBaz' => 'two',
        );
        $this->assertEquals($expected, ArrayTool::camelizeKeys($array));
    }

    public function testUnderscoreKeys() {
        $array = array(
            'fooBar' => 'one',
            'barBaz' => 'two',
        );
        $expected = array(
            'foo_bar' => 'one',
            'bar_baz' => 'two',
        );
        $this->assertEquals($expected, ArrayTool::underscoreKeys($array));
    }

    public function testGetHash() {
        $array = $this->getTestArray();
        $hash1 = ArrayTool::getHash($array);
        $hash2 = ArrayTool::getHash($array);
        $this->assertTrue(!empty($hash1) && !empty($hash2));
        $this->assertEquals($hash1, $hash2);

        $array['other'] = 'item';
        $hash3 = ArrayTool::getHash($array);
        $this->assertTrue(!empty($hash3));
        $this->assertNotEquals($hash1, $hash3);
    }

    protected function getTestArray() {
        return array(
            'foo' => 'test',
            'bar' => array(
                'something',
            ),
            'unsetMe' => 1,
            'baz' => array(
                'test' => array(
                    'unsetMe' => array(
                        'unsetMe' => 'test',
                    ),
                ),
            ),
        );
    }
}
