<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\ClassNotFoundException;
use const Morpho\Web\VENDOR;
use Morpho\Base\Node as BaseNode;

class ModuleProvider extends Node {
    public const SYSTEM_MODULE = VENDOR . '/system';
    /**
     * @var PathManager
     */
    protected $pathManager;

    public function __construct(PathManager $pathManager) {
        parent::__construct('ModuleProvider');
        $this->pathManager = $pathManager;
    }

    /**
     * @return string|false
     */
    protected function childNameToClass(string $name) {
        return $this->pathManager->moduleClass($name);
    }

    protected function loadChild(string $moduleName): BaseNode {
        $pathManager = $this->pathManager;
        $class = $this->childNameToClass($moduleName);
        if (false === $class) {
            throw new ClassNotFoundException("Unable to load the module '$moduleName'");
        }
        $pathManager->registerModuleAutoloader($moduleName);
        return new $class(
            $moduleName,
            new ModulePathManager($pathManager->moduleDirPath($moduleName))
        );
    }
}