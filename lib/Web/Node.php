<?php
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Base\Node as BaseNode;
use Morpho\Di\{IHasServiceManager, IServiceManager};

abstract class Node extends BaseNode implements IHasServiceManager {
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function append($node): void {
        parent::append($node);
        if ($node instanceof IHasServiceManager) {
            $node->setServiceManager($this->serviceManager);
        }
    }
}
