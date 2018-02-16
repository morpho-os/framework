<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\ModuleMetaIterator as BaseModuleMetaIterator;
use Morpho\Ioc\IServiceManager;
use Zend\Stdlib\ArrayUtils;

class ModuleMetaIterator extends BaseModuleMetaIterator {
    /**
     * @var array
     */
    protected $enabledModules;
    /**
     * @var array
     */
    protected $metaPatch;

    protected function init(IServiceManager $serviceManager): void {
        parent::init($serviceManager);
        $site = $serviceManager['site'];
        $siteConfig = $site->config();
        $this->enabledModules = array_flip(array_keys($siteConfig['modules']));
        $siteModuleName = $site->moduleName();
        $this->metaPatch = [
            $siteModuleName  => [
                'paths' => $siteConfig['paths'],
            ],
        ];
    }

    protected function filter(array $moduleMeta): bool {
        return parent::filter($moduleMeta) && isset($this->enabledModules[$moduleMeta['name']]);
    }

    protected function map(array $moduleMeta): array {
        $moduleName = $moduleMeta['name'];
        $moduleMeta = parent::map($moduleMeta);
        if (isset($this->metaPatch[$moduleName])) {
            $moduleMeta = ArrayUtils::merge($moduleMeta, $this->metaPatch[$moduleName]);
        }
        $moduleMeta['weight'] = $this->enabledModules[$moduleName] ?? 0;
        return $moduleMeta;
    }
}