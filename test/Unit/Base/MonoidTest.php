<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Base\IContainer;
use Morpho\Base\IMonoid;
use Morpho\Base\ISemigroup;
use Morpho\Base\Monoid;
use Morpho\Testing\TestCase;

class MonoidTest extends TestCase {
    private IMonoid $monoid;

    public function setUp(): void {
        parent::setUp();
        $this->monoid = new class extends Monoid {
            public function mappend(mixed $x, mixed $y): mixed {
                return $x . $y;
            }

            public function mempty(): mixed {
                return "";
            }
        };
    }

    public function testInterface() {
        $this->assertNotInstanceOf(IContainer::class, $this->monoid);
        $this->assertInstanceOf(ISemigroup::class, $this->monoid);
        $this->assertInstanceOf(IMonoid::class, $this->monoid);
    }

    public function testLeftIdentity() {
        $this->assertEquals("Foo", $this->monoid->mappend($this->monoid->mempty(), "Foo"));
    }

    public function testRightIdentity() {
        $this->assertEquals("Foo", $this->monoid->mappend("Foo", $this->monoid->mempty()));
    }

    public function testAssociativity() {
        $x = 'foo';
        $y = 'bar';
        $z = 'qux';
        $expected = 'foobarqux';
        $this->assertSame($expected, $this->monoid->mappend($x, $this->monoid->mappend($y, $z)));
        $this->assertSame($expected, $this->monoid->mappend($this->monoid->mappend($x, $y), $z));
    }

    public function testConcatenation() {
        $this->assertSame("Hello Haskell!", $this->monoid->mconcat(["Hello", " ", "Haskell", "!"]));
    }
}