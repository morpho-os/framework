<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Core;

use Morpho\Base\Node as BaseNode;
use Morpho\Ioc\{IHasServiceManager, IServiceManager};

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
