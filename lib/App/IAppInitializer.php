<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Ioc\IServiceManager;

interface IAppInitializer {
    public function init(IServiceManager $serviceManager): void;

    public function mkSite(\ArrayObject $appConfig): ISite;

    public function mkServiceManager(array $services): IServiceManager;

    public function mkFallbackErrorHandler(): callable;
}
