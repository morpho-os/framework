<?php
namespace MorphoTest\Base;

use Morpho\Base\Stack;
use Morpho\Test\TestCase;

class StackTest extends TestCase {
    public function testInterfaces() {
        $this->assertInstanceOf('\SplStack', new Stack());
    }

    public function testClear() {
        $stack = new Stack();
        $this->assertEquals(0, count($stack));

        $stack->push(1);
        $stack->push(2);

        $this->assertEquals(2, count($stack));

        $stack->clear();

        $this->assertEquals(0, count($stack));
    }

    public function testPeekThrowsExceptionForEmptyStack() {
        $stack = new Stack();
        $this->expectException('\RuntimeException');
        $stack->peek();
    }

    public function testPeek() {
        $stack = new Stack();
        $stack->push('foo');
        $stack->push('bar');
        $this->assertEquals('bar', $stack->peek());
        $this->assertEquals(2, $stack->count());
    }

    public function testReplace() {
        $stack = new Stack();
        $stack->push('foo');
        $stack->replace('bar');
        $this->assertEquals(1, $stack->count());
        $this->assertEquals('bar', $stack[0]);
    }
}
