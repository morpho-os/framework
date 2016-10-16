<?php
namespace MorphoTest\Base;

use Morpho\Base\Node;
use Morpho\Test\TestCase;

class NodeTest extends TestCase {
    public function setUp() {
        $this->node = new MyNode();
    }

    public function testGetLeaf() {
        $firstLevelChild = $this->node->addChild((new Node())->setName('firstLevel'));
        $secondLevelChild = $firstLevelChild->addChild((new Node())->setName('secondLevel'));
        $this->assertSame($secondLevelChild, $this->node->getLeaf('secondLevel'));
    }

    public function testGetLeaf_ThrowsExceptionForNotLeaf() {
        $this->expectException('\Morpho\Base\ObjectNotFoundException', "Unable to find a node with the name 'firstLevel' in leaf nodes.");
        $firstLevelChild = $this->node->addChild((new Node())->setName('firstLevel'));
        $secondLevelChild = $firstLevelChild->addChild((new Node())->setName('secondLevel'));
        $this->assertSame($secondLevelChild, $this->node->getLeaf('secondLevel'));
        $this->node->getLeaf('firstLevel');
    }

    public function testNewNode_WithoutTypeThrowsException() {
        $comp = new Node([]);
        $this->expectException('\Morpho\Base\EmptyPropertyException', "The property 'Morpho\\Base\\Node::type' is empty.");
        $comp->getType();
    }

    public function testHas() {
        $comp = (new Node())->setName('foo');

        $this->assertEquals(0, count($this->node));
        $this->assertFalse($this->node->hasChild('foo'));
        $this->assertFalse($this->node->hasChild($comp));

        $this->node->addChild($comp);

        $this->assertEquals(1, count($this->node));
        $this->assertTrue($this->node->hasChild('foo'));
        $this->assertTrue($this->node->hasChild($comp));
    }

    public function testAddChild_CantAddNodeWithoutName() {
        $node = new Node();
        $this->expectException('\RuntimeException', 'The node must have name.');
        $node->addChild(new Node());
    }

    public function testRemoveAll() {
        $this->assertEquals(0, count($this->node));
        $this->node->addChild((new Node())->setName('foo'));
        $this->node->addChild((new Node())->setName('bar'));
        $this->assertEquals(2, count($this->node));

        $this->node->removeAll();

        $this->assertEquals(0, count($this->node));
    }

    public function testAddChild_ByReferenceTwice() {
        $comp1 = (new Node())->setName('foo');
        $comp2 = (new Node())->setName('bar');
        $this->node->addChild($comp1);
        $this->node->addChild($comp2);
        $this->assertEquals(2, count($this->node));

        $this->assertTrue($this->node->hasChild('foo'));
        $this->assertTrue($this->node->hasChild('bar'));
    }

    public function testInterfaces() {
        $this->assertInstanceOf('\Morpho\Base\Object', $this->node);
        $this->assertInstanceOf('\RecursiveIterator', $this->node);
        $this->assertInstanceOf('\Countable', $this->node);
    }

    public function testRemoveChildAndIsEmpty() {
        $comp = (new Node())->setName('test');
        $this->assertTrue($this->node->isEmpty());

        $this->node->addChild($comp);

        $this->assertFalse($this->node->isEmpty());

        $this->node->removeChild($comp);

        $this->assertTrue($this->node->isEmpty());

        $this->node->addChild($comp);

        $this->assertFalse($this->node->isEmpty());

        $this->node->removeChild($comp->getName());

        $this->assertTrue($this->node->isEmpty());
    }

    public function testGetNonExistingChildThrowsException() {
        $this->expectException('\RuntimeException', "Unable to load a child node with the name 'some'");
        $this->node->getChild('some');
    }

    public function testGetExisting() {
        $comp = (new Node())->setName('foo');
        $this->node->addChild($comp);
        $this->assertSame($comp, $this->node->getChild('foo'));
    }

    public function testGetChildNodes() {
        $this->assertEquals([], $this->node->getChildNodes());
        $comp = (new Node())->setName('foo');
        $this->node->addChild($comp);
        $this->assertSame(['foo' => $comp], $this->node->getChildNodes());
    }

    public function testRecursiveIterator() {
        // Check initial state
        $this->assertNull($this->node->rewind());

        $this->assertFalse($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertNull($this->node->current());

        $item1 = (new Node())->setName('item1');
        $item2 = (new Node())->setName('item2');

        $this->node->addChild($item1);
        $this->node->addChild($item2);

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
        $node = (new Node())->setName('foo');
        $this->expectException('\LogicException', "Node doesn't have children.");
        $node->getChildren();
    }

    public function testRecursiveIterator_LeavesOnly() {
        $parentNode = (new Node())->setName('foo');
        $firstLevelChild = $parentNode->addChild(
            (new Node())->setName('bar')
        );
        $secondLevelChild = $firstLevelChild->addChild(
            (new Node())->setName('baz')
        );
        $it = new \RecursiveIteratorIterator($parentNode, \RecursiveIteratorIterator::LEAVES_ONLY);
        $i = 0;
        foreach ($it as $node) {
            $this->assertSame($secondLevelChild, $node);
            $i++;
        }
        $this->assertEquals(1, $i);
    }

    public function testRecursiveIterator_SelfFirst() {
        $parentNode = (new Node())->setName('foo');
        $firstLevelChild = $parentNode->addChild(
            (new Node())->setName('bar')
        );
        $firstLevelChild->addChild(
            (new Node())->setName('baz')
        );
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
        $parentNode = (new Node())->setName('foo');
        $firstLevelChild = $parentNode->addChild(
            (new Node())->setName('bar')
        );
        $secondLevelChild = $firstLevelChild->addChild(
            (new Node())->setName('baz')
        );
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

    public function testGetParent() {
        $node = (new Node())->setName('foo');
        $this->node->addChild($node);
        $this->assertSame($this->node, $node->getParent());
    }

    public function testAddChild_ReturnsAddedNode() {
        $item1 = (new Node())->setName('item1');
        $item2 = (new Node())->setName('item2');
        $this->assertSame($item1, $item2->addChild($item1));
    }

    public function testGetChild_CanLoadClass() {
        $node = new MyNode();
        $name = 'ChildNode';
        $childNode = $node->getChild($name);
        $class = __NAMESPACE__ . '\\' . $name;
        $this->assertEquals($class, get_class($childNode));
        $this->assertEquals($name, $childNode->getName());
    }

    protected function myObjectClassName() {
        return $this->getTestNs() . '\\MyNode';
    }
}

class MyNode extends Node {
}

class ChildNode extends Node {
    protected $name = 'someName';
}
