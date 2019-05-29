<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Ioc\ServiceManager as BaseServiceManager;

abstract class BootServiceManager extends BaseServiceManager {
    protected function mkSiteService(): ISite {
        return $this['siteFactory']->__invoke();
    }

    abstract protected function mkSiteFactoryService();
}
