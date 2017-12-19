<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Base;

use Morpho\Base\EmptyPropertyException;
use Morpho\Base\Node;
use Morpho\Test\TestCase;
use RuntimeException;

class NodeTest extends TestCase {
    private $node;

    public function setUp() {
        $this->node = new MyNode('foo');
    }

    public function testNewNode_ThrowsExceptionIfTypeIsEmpty() {
        $comp = new Node('foo');
        $this->expectException(EmptyPropertyException::class, "The property 'Morpho\\Base\\Node::type' is empty");
        $comp->type();
    }

    public function testAppend_NodeWithoutName() {
        $node = new Node('parent');
        $this->expectException(RuntimeException::class, 'The node must have name');
        $node->append(new Node(''));
    }

    public function testInterface() {
        $this->assertInstanceOf(\ArrayObject::class, $this->node);
    }

    public function testNamespace() {
        $node = new Node('test');
        $this->assertSame('Morpho\\Base', $node->namespace());

        $node = new MyNode('test');
        $this->assertSame(__NAMESPACE__, $node->namespace());
    }

    public function testAppend_SetsParent() {
        $node = new Node('foo');
        $this->assertNull($this->node->append($node));
        $this->assertSame($this->node, $node->parent());
    }

    public function testChild_CanLoadClass() {
        $name = 'ChildNode';
        $node = new MyNode($name);
        $childNode = $node->offsetGet($name);
        $class = __NAMESPACE__ . '\\' . $name;
        $this->assertEquals($class, get_class($childNode));
        $this->assertEquals($name, $childNode->name());
    }

    public function testIterator() {
        $node = new MyNode('foo');

        $node1 = new MyNode('bar');
        $node->append($node1);

        $node2 = new MyNode('baz');
        $node->append($node2);

        $node3 = new MyNode('pizza');
        $node->append($node3);

        $this->assertEquals(3, $node->count());

        $i = 0;
        foreach ($node as $child) {
            switch ($i) {
                case 0:
                    $this->assertSame($node1, $child);
                    break;
                case 1:
                    $this->assertSame($node2, $child);
                    break;
                case 2:
                    $this->assertSame($node3, $child);
                    break;
            }
            $i++;
        }
        $this->assertEquals(3, $i);
        $this->assertEquals(3, $node->count());
    }
}

class MyNode extends Node {
}

class ChildNode extends Node {
    protected $name = 'someName';
}
