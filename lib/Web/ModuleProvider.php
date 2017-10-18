<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\ClassNotFoundException;
use Morpho\Core\Node;
use Morpho\Base\Node as BaseNode;
use const Morpho\Core\VENDOR_DIR_NAME;

class ModuleProvider extends Node {
    /**
     * @var PathManager
     */
    protected $pathManager;

    /**
     * @var array
     */
    private $registeredModules = [];

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
        $this->registerModuleAutoloader($moduleName);
        return new $class(
            $moduleName,
            new ModulePathManager($pathManager->moduleDirPath($moduleName))
        );
    }

    private function registerModuleAutoloader(string $moduleName): void {
        if (!isset($this->registeredModules[$moduleName])) {
            // @TODO: Register simple autoloader, which must try to load the class using simple scheme, then
            // call Composer's autoloader in case of fail.
            require $this->pathManager->moduleDirPath($moduleName) . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
            $this->registeredModules[$moduleName] = true;
        }
    }
}
