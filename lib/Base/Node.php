<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use RuntimeException;

class Node extends ArrayObject {
    /**
     * Name must be unique among all child nodes.
     */
    protected $name;

    protected $type;

    /**
     * @var Node
     */
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

    public function append($node): void {
        /** @var Node node */
        if (empty($node->name())) {
            throw new RuntimeException("The node must have name");
        }
        $node->setParent($this);
        $this->offsetSet($node->name, $node);
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
     * @throws ObjectNotFoundException If unable to load the child.
     */
    protected function loadChild(string $name): Node {
        $class = $this->childNameToClass($name);
        if (!$class) {
            throw new ObjectNotFoundException("Unable to load a child node with the name '$name'");
        }
        return new $class($name);
    }

    /**
     * @return string|false
     */
    protected function childNameToClass(string $name) {
        $class = $this->namespace() . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}