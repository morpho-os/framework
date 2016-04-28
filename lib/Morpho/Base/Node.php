<?php
declare(strict_types = 1);

namespace Morpho\Base;

class Node extends Object implements \Countable, \RecursiveIterator {
    protected $children = [];

    /**
     * Name must be unique among all child nodes.
     */
    protected $name;

    protected $type;

    protected $parent;

    public function setName($name): Node {
        $this->name = $name;
        return $this;
    }

    public function getName(): string {
        if (null === $this->name) {
            throw new EmptyPropertyException($this, 'name');
        }
        return $this->name;
    }

    public function getType(): string {
        if (null === $this->type) {
            throw new EmptyPropertyException($this, 'type');
        }
        return $this->type;
    }

    public function addChild(Node $node): Node {
        if (!$node->getName()) {
            throw new \RuntimeException("The node must have name.");
        }
        $node->setParent($this);
        $this->children[$node->getName()] = $node;
        return $node;
    }

    /**
     * @param string|Node
     */
    public function removeChild($child)/*: void */ {
        if (is_string($child)) {
            unset($this->children[$child]);
        } else {
            $name = $child->getName();
            unset($this->children[$name]);
        }
    }

    public function removeAll()/*: void */ {
        $this->children = [];
    }

    /**
     * @param string|Node $child
     */
    public function hasChild($child): bool {
        if (is_string($child)) {
            $name = $child;
        } else {
            $name = $child->getName();
        }
        return isset($this->children[$name])
            || $this->childNameToClass($name) !== false;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return $this->count() == 0;
    }

    public function getChild(string $name): Node {
        if (!isset($this->children[$name])) {
            $node = $this->loadChild($name);
            return $this->addChild($node);
        }
        return $this->children[$name];
    }

    public function getLeaf(string $name): Node {
        foreach (new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::LEAVES_ONLY) as $node) {
            if ($node->getName() == $name) {
                return $node;
            }
        }
        throw new ObjectNotFoundException("Unable to find a node with the name '$name' in leaf nodes.");
    }

    public function getChildNodes(): array {
        return $this->children;
    }

    public function setParent(Node $parent) {
        $this->parent = $parent;
    }

    /**
     * @param string|null $name
     * @return Node|null
     */
    public function getParent($name = null) {
        $parent = $this->parent;
        if (null !== $name) {
            while ($parent && $parent->getName() != $name) {
                $parent = $parent->getParent($name);
            }
        }
        return $parent;
    }

    public function getParentByType(string $type) {
        $parent = $this->parent;
        while ($parent && $parent->getType() != $type) {
            $parent = $parent->getParentByType($type);
        }
        return $parent;
    }

    /**
     * @return \RecursiveIterator|null
     */
    public function getChildren() {
        if (!$this->hasChildren()) {
            throw new \LogicException("Node doesn't have children.");
        }
        return $this->current();
    }

    /**
     * @return bool Returns true if the current entry can be iterated over, otherwise returns false.
     */
    public function hasChildren() {
        $current = $this->current();
        return null !== $current && count($current) > 0;
    }

    /**
     * @return Node|null
     */
    public function current() {
        $current = current($this->children);
        return $current ? $current : null;
    }

    public function key() {
        return key($this->children);
    }

    public function next()/*: void */ {
        next($this->children);
    }

    public function rewind()/*: void */ {
        reset($this->children);
    }

    public function valid(): bool {
        return false !== current($this->children);
    }

    public function count(): int {
        return count($this->children);
    }

    /**
     * @throws ObjectNotFoundException If unable to load the child.
     */
    protected function loadChild(string $name): Node {
        $class = $this->childNameToClass($name);
        if (!$class) {
            throw new ObjectNotFoundException("Unable to load a child node with the name '$name'");
        }
        return (new $class())->setName($name);
    }

    /**
     * @return string|false
     */
    protected function childNameToClass(string $name) {
        $class = $this->getNamespace() . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}