<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\ArrayTool;

class ArrayToolTest extends TestCase {
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

    public function dataForCheckItems_Valid() {
        return [
            [
                ['foo' => 1],
                [],
                ['foo'],
            ],
            [
                ['foo' => 1],
                ['foo'],
                [],
            ],
            [
                ['foo' => 1, 'bar' => 2, 'baz' => 3],
                ['foo', 'baz'],
                ['bar'],
            ],
            [
                ['foo' => 1, 'baz' => '2'],
                ['foo'],
                ['foo', 'baz', 'bar']
            ],
        ];
    }

    /**
     * @dataProvider dataForCheckItems_Valid
     */
    public function testCheckItems_Valid($actual, $requiredKeys, $allowedKeys) {
        ArrayTool::checkItems($actual, $requiredKeys, $allowedKeys);
    }

    public function dataForCheckItems_RequiredItemsMissing() {
        return [
            [
                ['foo' => 1],
                ['foo', 'bar'],
                [],
            ],
            [
                ['bar' => 1],
                ['bar', 'baz'],
                ['foo', 'bar', 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider dataForCheckItems_RequiredItemsMissing
     */
    public function testCheckItems_RequiredItemsMissing($actual, $requiredKeys, $allowedKeys) {
        $this->setExpectedException('\RuntimeException', 'Required items are missing.');
        ArrayTool::checkItems($actual, $requiredKeys, $allowedKeys);
    }

    public function dataForCheckItems_NotAllowedItemsPresent() {
        return [
            [
                ['bar' => 1, 'foo' => 2],
                ['foo'],
                ['baz'],
            ],
        ];
    }

    /**
     * @dataProvider dataForCheckItems_NotAllowedItemsPresent
     */
    public function testCheckItems_NotAllowedItemsPresent($actual, $requiredKeys, $allowedKeys) {
        $this->setExpectedException('\RuntimeException', 'Not allowed items are present.');
        ArrayTool::checkItems($actual, $requiredKeys, $allowedKeys);
    }

    public function dataForCheckRequiredItems_Invalid() {
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
     * @dataProvider dataForCheckRequiredItems_Invalid
     */
    public function testCheckRequiredItems_Invalid($actual, $requiredKeys) {
        $this->setExpectedException('\RuntimeException', 'Required items are missing.');
        ArrayTool::checkRequiredItems($actual, $requiredKeys);
    }

    public function dataForCheckRequiredItems_Valid() {
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
     * @dataProvider dataForCheckRequiredItems_Valid
     */
    public function testCheckRequiredItems_Valid($actual, $requiredKeys) {
        ArrayTool::checkRequiredItems($actual, $requiredKeys);
    }

    public function dataForEnsureHasOnlyKeys_Invalid() {
        return [
            [
                ['foo' => '1', 'something' => 2],
                ['foo', 'bar', 'baz'],
            ],
            [
                ['foo' => '2', 'bar' => 2, 'baz' => 3, 'something' => 4],
                ['foo', 'bar', 'baz'],
            ],
        ];
    }

    /**
     * @dataProvider dataForEnsureHasOnlyKeys_Invalid
     */
    public function testCheckAllowed_Invalid($actual, $allowedKeys) {
        $this->setExpectedException('\RuntimeException', 'Not allowed items are present.');
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
        $this->setExpectedException('\RuntimeException', "Not allowed items are present.");
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
