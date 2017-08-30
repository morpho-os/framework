<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Base;

use Morpho\Base\EmptyPropertyException;
use Morpho\Base\Node;
use Morpho\Base\Object;
use Morpho\Test\TestCase;
use RuntimeException;

class NodeTest extends TestCase {
    private $node;

    public function setUp() {
        $this->node = new MyNode('foo');
    }

    public function testRecursiveTraversal() {
        $this->markTestIncomplete();
    }

/*    public function testLeaf() {
        $firstLevelChild = $this->node->append(new Node('firstLevel'));
        $secondLevelChild = $firstLevelChild->append(new Node('secondLevel'));
        $this->assertSame($secondLevelChild, $this->node->leaf('secondLevel'));
    }

    public function testLeaf_ThrowsExceptionForNotLeaf() {
        $this->expectException(ObjectNotFoundException::class, "Unable to find a node with the name 'firstLevel' in leaf nodes.");
        $firstLevelChild = $this->node->append(new Node('firstLevel'));
        $secondLevelChild = $firstLevelChild->append(new Node('secondLevel'));
        $this->assertSame($secondLevelChild, $this->node->leaf('secondLevel'));
        $this->node->leaf('firstLevel');
    }

    public function testRecursiveIterator() {
        // Check initial state
        $this->assertNull($this->node->rewind());

        $this->assertFalse($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertNull($this->node->current());

        $item1 = new Node('item1');
        $item2 = new Node('item2');

        $this->node->append($item1);
        $this->node->append($item2);

        // Reset pointer
        $this->assertNull($this->node->rewind());

        // 0 offset
        $this->assertTrue($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertSame($item1, $this->node->current());

        // Move pointer
        $this->assertNull($this->node->next());

        // 1 offset
        $this->assertTrue($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertSame($item2, $this->node->current());

        // Move pointer
        $this->assertNull($this->node->next());

        $this->assertFalse($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertNull($this->node->current());
    }

    public function testGetChildren_ThrowsLogicExceptionWhenNodeDoesNotHaveChildren() {
        $node = new Node('foo');
        $this->expectException(LogicException::class, "Node doesn't have children");
        $node->getChildren();
    }

    public function testRecursiveIterator_LeavesOnly() {
        $parentNode = new Node('foo');
        $firstLevelChild = $parentNode->append(new Node('bar'));
        $secondLevelChild = $firstLevelChild->append(new Node('baz'));
        $it = new \RecursiveIteratorIterator($parentNode, \RecursiveIteratorIterator::LEAVES_ONLY);
        $i = 0;
        foreach ($it as $node) {
            $this->assertSame($secondLevelChild, $node);
            $i++;
        }
        $this->assertEquals(1, $i);
    }

    public function testRecursiveIterator_SelfFirst() {
        $parentNode = new Node('foo');
        $firstLevelChild = $parentNode->append(new Node('bar'));
        $firstLevelChild->append(new Node('baz'));
        $it = new \RecursiveIteratorIterator($parentNode, \RecursiveIteratorIterator::SELF_FIRST);
        $i = 0;
        foreach ($it as $node) {
            if ($i == 0) {
                $this->assertSame($firstLevelChild, $node);
            }
            $i++;
        }
        $this->assertEquals(2, $i);
    }

    public function testRecursiveIterator_ChildFirst() {
        $parentNode = new Node('foo');
        $firstLevelChild = $parentNode->append(new Node('bar'));
        $secondLevelChild = $firstLevelChild->append(new Node('baz'));
        $it = new \RecursiveIteratorIterator($parentNode, \RecursiveIteratorIterator::CHILD_FIRST);
        $i = 0;
        foreach ($it as $node) {
            if ($i == 0) {
                $this->assertSame($secondLevelChild, $node);
            }
            $i++;
        }
        $this->assertEquals(2, $i);
    }
*/

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
        $this->assertInstanceOf(Object::class, $this->node);
    }

    public function testAppend_SetsParent() {
        $node = new Node('foo');
        $this->assertSame($this->node, $this->node->append($node));
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
