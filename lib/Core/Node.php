<?php
declare(strict_types = 1);

namespace Morpho\Core;

use Morpho\Base\Node as BaseNode;
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
}