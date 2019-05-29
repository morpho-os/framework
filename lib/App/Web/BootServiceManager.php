<?php declare(strict_types=1);
namespace Morpho\App\Web;

use Morpho\App\BootServiceManager as BaseBootServiceManager;

class BootServiceManager extends BaseBootServiceManager {
    protected function mkSiteFactoryService() {
        return new SiteFactory();
    }
}
