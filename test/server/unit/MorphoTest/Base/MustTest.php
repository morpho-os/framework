<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\Must;

class MustTest extends TestCase {
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
        $this->expectException('\RuntimeException', 'Required items are missing');
        Must::haveKeys($actual, $requiredKeys);
    }

    public function dataForHasKeys_Valid_DoesNotThrowException() {
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
     * @dataProvider dataForHasKeys_Valid_DoesNotThrowException
     */
    public function testHasKeys_Valid_DoesNotThrowException($actual, $requiredKeys) {
        Must::haveKeys($actual, $requiredKeys);
        $this->markTestAsNotRisky();
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
        $this->expectException('\RuntimeException', 'Not allowed items are present: ' . implode(', ', $notAllowedItems));
        Must::haveOnlyKeys($actual, $allowedKeys);
    }

    public function dataForHasOnlyKeys_Valid_DoesNotThrowException() {
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
     * @dataProvider dataForHasOnlyKeys_Valid_DoesNotThrowException
     */
    public function testHasOnlyKeys_Valid_DoesNotThrowException($actual, $allowedKeys) {
        Must::haveOnlyKeys($actual, $allowedKeys);
        $this->markTestAsNotRisky();
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
        $this->expectException('\RuntimeException', 'The value is not one of the provided values');
        Must::beOneOf($needle, $haystack);
    }

    public function dataForIsOneOf_Valid_DoesNotThrowException() {
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
     * @dataProvider dataForIsOneOf_Valid_DoesNotThrowException
     */
    public function testIsOneOf_Valid_DoesNotThrowException($needle, $haystack) {
        Must::beOneOf($needle, $haystack);
        $this->markTestAsNotRisky();
    }
}