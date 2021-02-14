<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Base\Enum;
use Morpho\Testing\TestCase;

class EnumTest extends TestCase {
    public function testMembers_DoesNotIncludeNonPublicMembers() {
        $members = MyEnum::members();
        $this->assertSame([
            'PUB_FOO' => 'foo',
            'PUB_BAZ' => 'baz',
        ], $members);
    }

    public function testHasVal() {
        $this->assertTrue(MyEnum::hasVal('foo'));
        $this->assertFalse(MyEnum::hasVal('PUB_FOO'));
        $this->assertFalse(MyEnum::hasVal('abc'));
    }

    public function testHasName() {
        $this->assertFalse(MyEnum::hasName('foo'));
        $this->assertTrue(MyEnum::hasName('PUB_FOO'));
        $this->assertFalse(MyEnum::hasName('PRIV_BAR'));
        $this->assertFalse(MyEnum::hasName('PROT_BAR'));
        $this->assertFalse(MyEnum::hasName('abc'));
    }

    public function testVals() {
        $this->assertSame(['foo', 'baz'], MyEnum::vals());
    }

    public function testNames() {
        $this->assertSame(['PUB_FOO', 'PUB_BAZ'], MyEnum::names());
    }
}

class MyEnum extends Enum {
    public const PUB_FOO = 'foo';
    private const PRIV_BAR = 'bar';
    protected const PROT_BAR = 'qux';
    public const PUB_BAZ = 'baz';
}