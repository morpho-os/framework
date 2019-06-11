<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Morpho\App\AppInitializer as BaseAppInitializer;

class AppInitializer extends BaseAppInitializer {
    public function init(): void {
        Environment::init();
        $siteConfig = $this->serviceManager['site']->config();
        $this->applySiteConfig($siteConfig);
        $this->serviceManager['errorHandler']->register();
    }
}