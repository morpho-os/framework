<?php
namespace MorphoTest\Base;

use Morpho\Base\Node;
use Morpho\Test\TestCase;

class NodeTest extends TestCase {
    public function setUp() {
        $this->node = new MyNode();
    }

    public function testGetLeaf() {
        $firstLevelChild = $this->node->add(new Node(['name' => 'firstLevel']));
        $secondLevelChild = $firstLevelChild->add(new Node(['name' => 'secondLevel']));
        $this->assertSame($secondLevelChild, $this->node->getLeaf('secondLevel'));
    }

    public function testGetNotLeafNodeThrowsException() {
        $this->setExpectedException('\Morpho\Base\ObjectNotFoundException', "Unable to find a node with the name 'firstLevel' in leaf nodes.");
        $firstLevelChild = $this->node->add(new Node(['name' => 'firstLevel']));
        $secondLevelChild = $firstLevelChild->add(new Node(['name' => 'secondLevel']));
        $this->assertSame($secondLevelChild, $this->node->getLeaf('secondLevel'));
        $this->node->getLeaf('firstLevel');
    }

    public function testNodeWithoutTypeThrowsException() {
        $comp = new Node([]);
        $this->setExpectedException('\Morpho\Base\EmptyPropertyException', "The property 'Morpho\\Base\\Node::type' is empty.");
        $comp->getType();
    }

    public function testHas() {
        $comp = new Node(['name' => 'foo']);

        $this->assertEquals(0, count($this->node));
        $this->assertFalse($this->node->has('foo'));
        $this->assertFalse($this->node->has($comp));

        $this->node->add($comp);

        $this->assertEquals(1, count($this->node));
        $this->assertTrue($this->node->has('foo'));
        $this->assertTrue($this->node->has($comp));
    }

    public function testCantAddNodeWithoutName() {
        $node = new Node();
        $this->setExpectedException('\RuntimeException', 'The node must have name.');
        $node->add(new Node(['name' => '']));
    }

    public function testRemoveAll() {
        $this->assertEquals(0, count($this->node));
        $this->node->add(new Node(['name' => 'foo']));
        $this->node->add(new Node(['name' => 'bar']));
        $this->assertEquals(2, count($this->node));

        $this->node->removeAll();

        $this->assertEquals(0, count($this->node));
    }

    public function testAddByReferenceTwice() {
        $comp1 = new Node(['name' => 'foo']);
        $comp2 = new Node(['name' => 'bar']);
        $this->node->add($comp1);
        $this->node->add($comp2);
        $this->assertEquals(2, count($this->node));

        $this->assertTrue($this->node->has('foo'));
        $this->assertTrue($this->node->has('bar'));
    }

    public function testInterfaces() {
        $this->assertInstanceOf('\Morpho\Base\Object', $this->node);
        $this->assertInstanceOf('\RecursiveIterator', $this->node);
        $this->assertInstanceOf('\Countable', $this->node);
    }

    public function testRemoveAndIsEmpty() {
        $comp = new Node(['name' => 'test']);
        $this->assertTrue($this->node->isEmpty());

        $this->node->add($comp);

        $this->assertFalse($this->node->isEmpty());

        $this->node->remove($comp);

        $this->assertTrue($this->node->isEmpty());

        $this->node->add($comp);

        $this->assertFalse($this->node->isEmpty());

        $this->node->remove($comp->getName());

        $this->assertTrue($this->node->isEmpty());
    }

    public function testGetNonExistingChildThrowsException() {
        $this->setExpectedException('\RuntimeException', "Unable to load a node with the 'some' name, check that class exists.");
        $this->node->get('some');
    }

    public function testGetExisting() {
        $comp = new Node(['name' => 'foo']);
        $this->node->add($comp);
        $this->assertSame($comp, $this->node->get('foo'));
    }

    public function testGetAll() {
        $this->assertEquals([], $this->node->getAll());
        $comp = new Node(['name' => 'foo']);
        $this->node->add($comp);
        $this->assertSame(['foo' => $comp], $this->node->getAll());
    }

    public function testRecursiveIteratorMethods() {
        // Check initial state
        $this->assertNull($this->node->rewind());

        $this->assertFalse($this->node->valid());
        $this->assertFalse($this->node->hasChildren());
        $this->assertNull($this->node->current());

        $item1 = new Node(['name' => 'item1']);
        $item2 = new Node(['name' => 'item2']);

        $this->node->add($item1);
        $this->node->add($item2);

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

    public function testGetChildrenThrowsLogicExceptionWhenNodeDoesNotHaveChildren() {
        $node = new Node(['name' => 'foo']);
        $this->setExpectedException('\LogicException', "Node doesn't have children.");
        $node->getChildren();
    }

    public function testRecursiveIteratorLeavesOnly() {
        $parentNode = new Node(['name' => 'foo']);
        $firstLevelChild = $parentNode->add(
            new Node(['name' => 'bar'])
        );
        $secondLevelChild = $firstLevelChild->add(
            new Node(['name' => 'baz'])
        );
        $it = new \RecursiveIteratorIterator($parentNode, \RecursiveIteratorIterator::LEAVES_ONLY);
        $i = 0;
        foreach ($it as $node) {
            $this->assertSame($secondLevelChild, $node);
            $i++;
        }
        $this->assertEquals(1, $i);
    }

    public function testRecursiveIteratorSelfFirst() {
        $parentNode = new Node(['name' => 'foo']);
        $firstLevelChild = $parentNode->add(
            new Node(['name' => 'bar'])
        );
        $secondLevelChild = $firstLevelChild->add(
            new Node(['name' => 'baz'])
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

    public function testRecursiveIteratorChildFirst() {
        $parentNode = new Node(['name' => 'foo']);
        $firstLevelChild = $parentNode->add(
            new Node(['name' => 'bar'])
        );
        $secondLevelChild = $firstLevelChild->add(
            new Node(['name' => 'baz'])
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
        $node = new Node(['name' => 'foo']);
        $this->node->add($node);
        $this->assertSame($this->node, $node->getParent());
    }

    public function testAddReturnsAddedNode() {
        $item1 = new Node(['name' => 'item1']);
        $item2 = new Node(['name' => 'item2']);
        $this->assertSame($item1, $item2->add($item1));
    }

    public function testCanLoadSubnodes() {
        $node = new MyNode();
        $loadableNode = $node->get('myLoadable');
        $this->assertEquals('myLoadable', $loadableNode->getName());
    }

    protected function myObjectClassName() {
        return $this->getTestNs() . '\\MyNode';
    }
}

class MyNode extends Node {
    protected $loadable = array(
        'myLoadable' => 'MorphoTest\\Base\\MyLoadableNode',
    );
}

class MyLoadableNode extends Node {
    protected $name = 'someName';
}
