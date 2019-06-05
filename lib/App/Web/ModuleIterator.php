<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\ModuleIterator as BaseModuleIterator;
use Morpho\Ioc\IServiceManager;
use Zend\Stdlib\ArrayUtils;

class ModuleIterator extends BaseModuleIterator {
    /**
     * @var array
     */
    protected $enabledModules;
    /**
     * @var array
     */
    protected $patch;

    protected function init(IServiceManager $serviceManager): void {
        parent::init($serviceManager);
        $site = $serviceManager['site'];
        $siteConfig = $site->config();
        $this->enabledModules = \array_flip(\array_keys($siteConfig['module']));
        $siteModuleName = $site->moduleName();
        $this->patch = [
            $siteModuleName  => [
                'path' => $siteConfig['path'],
            ],
        ];
    }

    protected function filter(array $module): bool {
        return parent::filter($module) && isset($this->enabledModules[$module['name']]);
    }

    protected function map(array $module): array {
        $moduleName = $module['name'];
        $module = parent::map($module);
        if (isset($this->patch[$moduleName])) {
            $module = ArrayUtils::merge($module, $this->patch[$moduleName]);
        }
        $module['weight'] = $this->enabledModules[$moduleName] ?? 0;
        return $module;
    }
}
