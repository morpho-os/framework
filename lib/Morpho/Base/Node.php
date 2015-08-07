<?php
declare(strict_types=1);

namespace Morpho\Base;

class Node extends Object implements \Countable, \RecursiveIterator {
    protected $children = array();

    /**
     * Name must be unique among all child nodes.
     */
    protected $name;

    protected $type;

    protected $parent;

    protected $loadable = array();

    public function setName($name): Node {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     * @throws EmptyPropertyException
     */
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
     * @return void
     */
    public function removeChild($child) {
        if (is_string($child)) {
            unset($this->children[$child]);
        } else {
            $name = $child->getName();
            unset($this->children[$name]);
        }
    }

    /**
     * @return void
     */
    public function removeAll() {
        $this->children = array();
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

        return isset($this->children[$name]) || $this->isLoadable($name);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return $this->count() == 0;
    }

    public function getChild(string $name): Node {
        if (!isset($this->children[$name])) {
            $node = $this->tryLoad($name);
            if (!$node) {
                throw new ObjectNotFoundException(
                    "Unable to load a node with the '$name' name, check that class exists."
                );
            }
            return $this->addChild($node);
        }

        return $this->children[$name];
    }

    public function getLeaf(string $name): Node {
        foreach (new \RecursiveIteratorIterator($this) as $node) {
            if ($node->getName() == $name) {
                return $node;
            }
        }
        throw new ObjectNotFoundException("Unable to find a node with the name '$name' in leaf nodes.");
    }

    /**
     * @return array
     */
    public function getAll() {
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
     * @return RecursiveIterator|null
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

    /**
     * @return string
     */
    public function key() {
        return key($this->children);
    }

    /**
     * @return void
     */
    public function next() {
        next($this->children);
    }

    /**
     * @return void
     */
    public function rewind() {
        reset($this->children);
    }

    /**
     * @return bool
     */
    public function valid() {
        return false !== current($this->children);
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->children);
    }

    protected function tryLoad(string $name) {
        if ($this->isLoadable($name)) {
            $class = $this->loadable[$name];
            return (new $class())->setName($name);
        }
        return false;
    }

    protected function isLoadable(string $name): bool {
        return isset($this->loadable[$name]);
    }
}
