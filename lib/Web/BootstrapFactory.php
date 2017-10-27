<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

//use Morpho\Core\IModuleIndexer;
use Morpho\Di\IServiceManager;

class BootstrapFactory {
    public function newSite($appConfig): Site {
        return (new SiteFactory())($appConfig);
    }

    public function newServiceManager($services): IServiceManager {
        return new ServiceManager($services);
    }
}