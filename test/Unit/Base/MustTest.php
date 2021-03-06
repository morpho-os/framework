<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use InvalidArgumentException;
use Morpho\Base\Must;
use Morpho\Testing\TestCase;
use RuntimeException;

use function implode;

class MustTest extends TestCase {
    public function testContain_String_Valid() {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull(Must::contain('foo/bar', '/'));
    }

    public function testContain_String_Invalid() {
        $this->expectException(RuntimeException::class, 'A haystack does not contain a needle');
        Must::contain('foo-bar', '/');
    }

    public function dataContain_Array_Valid() {
        return [
            [
                [null, 0, '', false],
                '',
            ],
            [
                ['bar', 'baz', 'foo'],
                'foo',
            ],
            [
                ['foo', ['bar']],
                ['bar'],
            ],
        ];
    }

    /**
     * @dataProvider dataContain_Array_Valid
     */
    public function testContain_Array_Valid($haystack, $needle) {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull(Must::contain($haystack, $needle));
    }

    public function dataContain_Array_Invalid() {
        return [
            [
                [],
                null,
            ],
            [
                [null, 0, false],
                '',
            ],
            [
                ['bar', 'baz'],
                'foo',
            ],
        ];
    }

    /**
     * @dataProvider dataContain_Array_Invalid
     */
    public function testContain_Array_Invalid($haystack, $needle) {
        $this->expectException(RuntimeException::class, 'A haystack does not contain a needle');
        Must::contain($haystack, $needle);
    }

    public function testBeNotFalse_ReturnsPassedArgumentIfNotFalse() {
        $this->assertSame(STDERR, Must::beNotFalse(STDERR));
    }

    public function testBeNotFalse_ThrowsExceptionIfFalse() {
        $this->expectException(RuntimeException::class);
        Must::beNotFalse(false);
    }

    public function testBeEmpty_SingleArg_ThrowsExceptionOnNonEmptyValue() {
        $this->expectException(RuntimeException::class, "The value must be empty");
        Must::beEmpty('abc');
    }

    public function testBeEmpty_MultipleArgs_ThrowsExceptionOnNonEmptyValue() {
        $this->expectException(RuntimeException::class, "The value must be empty");
        Must::beEmpty("", "abc");
    }

    public function testBeEmpty_ReturnsEmptyValues() {
        $v = ['', null, 0, false];
        $this->assertSame($v, Must::beEmpty(...$v));

        $v = '';
        $this->assertSame($v, Must::beEmpty($v));

        $v = null;
        $this->assertSame($v, Must::beEmpty($v));

        $v = 0;
        $this->assertSame($v, Must::beEmpty($v));

        $v = false;
        $this->assertSame($v, Must::beEmpty($v));

        $v = [];
        $this->assertSame($v, Must::beEmpty($v));

        $v = 0.0;
        $this->assertSame($v, Must::beEmpty($v));
    }

    public function testBeEmpty_ThrowsExceptionOnEmptyArgs() {
        $this->expectException(InvalidArgumentException::class, "Empty arguments");
        Must::beEmpty();
    }

    public function dataBeNotEmpty_SingleArg_ThrowsExceptionOnEmptyValue() {
        return [
            [
                '',
                false,
                null,
                0,
                0.0,
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataBeNotEmpty_SingleArg_ThrowsExceptionOnEmptyValue
     */
    public function testBeNotEmpty_SingleArg_ThrowsExceptionOnEmptyValue($v) {
        $this->expectException(RuntimeException::class, "The value must be non empty");
        Must::beNotEmpty($v);
    }

    public function testBeNotEmpty_MultipleArgs_ThrowsExceptionOnEmptyValue() {
        $this->expectException(RuntimeException::class, "The value must be non empty");
        Must::beNotEmpty("abc", "");
    }

    public function testBeNotEmpty_ReturnsValues() {
        $v = ['foo', 123, 3.14, ["Hello"]];
        $this->assertSame($v, Must::beNotEmpty(...$v));
        $this->assertSame($v, Must::beNotEmpty($v));


        $v = 'abc';
        $this->assertSame($v, Must::beNotEmpty($v));
    }

    public function testBeNotEmpty_ThrowsExceptionOnEmptyArgs() {
        $this->expectException(InvalidArgumentException::class, "Empty arguments");
        Must::beNotEmpty();
    }

    public function dataHasKeys_Invalid() {
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
     * @dataProvider dataHasKeys_Invalid
     */
    public function testHasKeys_Invalid($actual, $requiredKeys) {
        $this->expectException('\RuntimeException', 'Required items are missing');
        Must::haveKeys($actual, $requiredKeys);
    }

    public function dataHasKeys_Valid_DoesNotThrowException() {
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
     * @dataProvider dataHasKeys_Valid_DoesNotThrowException
     */
    public function testHasKeys_Valid_DoesNotThrowException($actual, $requiredKeys) {
        Must::haveKeys($actual, $requiredKeys);
        $this->markTestAsNotRisky();
    }

    public function dataHasOnlyKeys_Invalid() {
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
     * @dataProvider dataHasOnlyKeys_Invalid
     */
    public function testCheckAllowed_Invalid($actual, $allowedKeys, $notAllowedItems) {
        $this->expectException(
            '\RuntimeException',
            'Not allowed items are present: ' . implode(', ', $notAllowedItems)
        );
        Must::haveOnlyKeys($actual, $allowedKeys);
    }

    public function dataHasOnlyKeys_Valid_DoesNotThrowException() {
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
     * @dataProvider dataHasOnlyKeys_Valid_DoesNotThrowException
     */
    public function testHasOnlyKeys_Valid_DoesNotThrowException($actual, $allowedKeys) {
        Must::haveOnlyKeys($actual, $allowedKeys);
        $this->markTestAsNotRisky();
    }
}
