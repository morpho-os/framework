<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Core;

use Morpho\Base\Node as BaseNode;
use Morpho\Di\{IServiceManagerAware, IServiceManager};

abstract class Node extends BaseNode implements IServiceManagerAware {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    public function append($node): BaseNode {
        parent::append($node);
        if ($node instanceof IServiceManagerAware) {
            $node->setServiceManager($this->serviceManager);
        }
        return $this;
    }
}
