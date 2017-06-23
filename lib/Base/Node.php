<?php
declare(strict_types = 1);
namespace Morpho\Base;

use RecursiveIterator;
use RuntimeException;

class Node extends Object {
    protected $children = [];

    /**
     * Name must be unique among all child nodes.
     */
    protected $name;

    protected $type;

    protected $parent;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function name(): string {
        return $this->name;
    }

    public function type(): string {
        if (null === $this->type) {
            throw new EmptyPropertyException($this, 'type');
        }
        return $this->type;
    }

    public function append($node): self {
        if (empty($node->name())) {
            throw new RuntimeException("The node must have name");
        }
        $node->setParent($this);
        $this->offsetSet($node->name, $node);
        return $this;
    }

    public function offsetExists($name): bool {
        return parent::offsetExists($name) || false !== $this->childNameToClass($name);
    }

    public function offsetGet($key) {
        if (!parent::offsetExists($key)) {
            $node = $this->loadChild($key);
            $this->append($node);
            return $node;
        }
        return parent::offsetGet($key);
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

    // @TODO: Merge with parent()
    public function parentByType(string $type): ?Node {
        $parent = $this->parent;
        while ($parent && $parent->type() !== $type) {
            $parent = $parent->parentByType($type);
        }
        return $parent;
    }

    /**
     * @return RecursiveIterator|null
    public function getChildren() {
        if (!$this->hasChildren()) {
            throw new LogicException("Node doesn't have children.");
        }
        return $this->current();
    }
 */

    /**
     * @return bool Returns true if the current entry can be iterated over, otherwise returns false.
    public function hasChildren() {
        $current = $this->current();
        return null !== $current && count($current) > 0;
    }
     */

    /**
     * @throws ObjectNotFoundException If unable to load the child.
     */
    protected function loadChild(string $name): Node {
        $class = $this->childNameToClass($name);
        if (!$class) {
            throw new ObjectNotFoundException("Unable to load a child node with the name '$name'");
        }
        return (new $class($name));
    }

    /**
     * @return string|false
     */
    protected function childNameToClass(string $name) {
        $class = $this->namespace() . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}