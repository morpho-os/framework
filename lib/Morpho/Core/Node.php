<?php
declare(strict_types=1);

namespace Morpho\Core;

use Morpho\Base\{Node as BaseNode, ObjectNotFoundException};
use Morpho\Di\{IServiceManagerAware, IServiceManager};

abstract class Node extends BaseNode implements IServiceManagerAware {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function addChild(BaseNode $node): BaseNode {
        $node = parent::addChild($node);
        if ($node instanceof IServiceManagerAware) {
            $node->setServiceManager($this->serviceManager);
        }
        return $node;
    }

    protected function isLoadable(string $name): bool {
        return parent::isLoadable($name) || class_exists($this->nameToClass($name));
    }

    protected function tryLoad(string $name) {
        if (parent::isLoadable($name)) {
            return parent::tryLoad($name);
        }
        $class = $this->nameToClass($name);
        if (!class_exists($class)) {
            throw new ObjectNotFoundException(
                "Unable to load a node with the '$name' name, check that the class '$class' exists."
            );
        }
        return (new $class())->setName($name);
    }

    abstract protected function nameToClass(string $name): string;
}
