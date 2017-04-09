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

    public function setName(string $name): Node {
        $this->name = $name;
        return $this;
    }

    public function name(): string {
        return $this->name;
    }

    public function hasName(): bool {
        return !empty($this->name);
    }

    public function type(): string {
        if (null === $this->type) {
            throw new EmptyPropertyException($this, 'type');
        }
        return $this->type;
    }

    public function addChild(Node $node): Node {
        if (!$node->hasName()) {
            throw new \RuntimeException("The node must have name.");
        }
        $node->setParent($this);
        $this->children[$node->name()] = $node;
        return $node;
    }

    /**
     * @param string|Node
     */
    public function removeChild($child): void {
        if (is_string($child)) {
            unset($this->children[$child]);
        } else {
            $name = $child->name();
            unset($this->children[$name]);
        }
    }

    public function removeAll(): void {
        $this->children = [];
    }

    /**
     * @param string|Node $child
     */
    public function hasChild($child): bool {
        if (is_string($child)) {
            $name = $child;
        } else {
            $name = $child->name();
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

    public function child(string $name): Node {
        if (!isset($this->children[$name])) {
            $node = $this->loadChild($name);
            return $this->addChild($node);
        }
        return $this->children[$name];
    }

    public function leaf(string $name): Node {
        foreach (new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::LEAVES_ONLY) as $node) {
            if ($node->name() == $name) {
                return $node;
            }
        }
        throw new ObjectNotFoundException("Unable to find a node with the name '$name' in leaf nodes.");
    }

    public function childNodes(): array {
        return $this->children;
    }

    public function setParent(Node $parent): void {
        $this->parent = $parent;
    }

    public function parent(?string $name = null): ?Node {
        $parent = $this->parent;
        if (null !== $name) {
            while ($parent && $parent->name() !== $name) {
                $parent = $parent->parent($name);
            }
        }
        return $parent;
    }

    public function parentByType(string $type): ?Node {
        $parent = $this->parent;
        while ($parent && $parent->type() !== $type) {
            $parent = $parent->parentByType($type);
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

    public function next(): void {
        next($this->children);
    }

    public function rewind(): void {
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
        $class = $this->namespace() . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}