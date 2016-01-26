<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\Assert;

class AssertTest extends TestCase {
    public function dataForHasKeys_Invalid() {
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
     * @dataProvider dataForHasKeys_Invalid
     */
    public function testHasKeys_Invalid($actual, $requiredKeys) {
        $this->setExpectedException('\RuntimeException', 'Required items are missing');
        Assert::HasKeys($actual, $requiredKeys);
    }

    public function dataForHasKeys_Valid() {
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
            ],
        ];
    }

    /**
     * @dataProvider dataForHasKeys_Valid
     */
    public function testHasKeys_Valid($actual, $requiredKeys) {
        Assert::hasKeys($actual, $requiredKeys);
    }

    public function dataForHasOnlyKeys_Invalid() {
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
     * @dataProvider dataForHasOnlyKeys_Invalid
     */
    public function testCheckAllowed_Invalid($actual, $allowedKeys, $notAllowedItems) {
        $this->setExpectedException('\RuntimeException', 'Not allowed items are present: ' . implode(', ', $notAllowedItems));
        Assert::HasOnlyKeys($actual, $allowedKeys);
    }

    public function dataForHasOnlyKeys_Valid() {
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
            ],
        ];
    }

    /**
     * @dataProvider dataForHasOnlyKeys_Valid
     */
    public function testHasOnlyKeys_Valid($actual, $allowedKeys) {
        Assert::HasOnlyKeys($actual, $allowedKeys);
    }

    public function dataForIsOneOf_Invalid() {
        return [
            [
                null,
                []
            ],
            [
                '',
                [null, 0, false]
            ],
            [
                'foo',
                ['bar', 'baz']
            ],
        ];
    }

    /**
     * @dataProvider dataForIsOneOf_Invalid
     */
    public function testIsOneOf_Invalid($needle, $haystack) {
        $this->setExpectedException('\RuntimeException', 'The value is not one of the provided values');
        Assert::isOneOf($needle, $haystack);
    }

    public function dataForIsOneOf_Valid() {
        return [
            [
                '',
                [null, 0, '', false]
            ],
            [
                'foo',
                ['bar', 'baz', 'foo']
            ],
        ];
    }

    /**
     * @dataProvider dataForIsOneOf_Valid
     */
    public function testIsOneOf_Valid($needle, $haystack) {
        Assert::isOneOf($needle, $haystack);
    }
}