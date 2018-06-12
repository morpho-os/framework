<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\ISite;
use Morpho\App\Site;
use Morpho\Base\NotImplementedException;
use Morpho\Ioc\IServiceManager;
use Morpho\App\AppInitializer as BaseAppInitializer;

class AppInitializer extends BaseAppInitializer {
    public function init(IServiceManager $serviceManager): void {
        Environment::init();
        $serviceManager['errorHandler']->register();
        parent::init($serviceManager);
    }

    public function mkSite(\ArrayObject $appConfig): ISite {
        return new Site($appConfig['siteModule'], $appConfig, $appConfig['siteHostName']);
    }

    public function mkServiceManager(array $services): IServiceManager {
        return new ServiceManager($services);
    }

    public function mkFallbackErrorHandler(): callable {
        throw new NotImplementedException('@TODO');
    }
}
