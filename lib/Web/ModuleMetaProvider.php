<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\ModuleMetaProvider as BaseModuleMetaProvider;
use Morpho\Ioc\IServiceManager;

class ModuleMetaProvider extends BaseModuleMetaProvider {
    protected function init(IServiceManager $serviceManager): void {
        parent::init($serviceManager);
        $site = $serviceManager->get('site');
        $siteConfig = $site->config();
        $this->enabledModules = array_flip(array_keys($siteConfig['modules']));
        $this->metaPatch = [$site->moduleName() => $siteConfig->getArrayCopy()];
    }
}