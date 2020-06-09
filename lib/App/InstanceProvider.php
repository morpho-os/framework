<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use function Morpho\Base\requireFile;
use Morpho\Base\IFn;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;

abstract class InstanceProvider implements IFn {
    protected ModuleIndex $moduleIndex;

    private array $registeredModules = [];

    private IServiceManager $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->moduleIndex = $serviceManager['serverModuleIndex'];
        $this->serviceManager = $serviceManager;
    }

    /**
     * @param Request $request
     * @return callable|false
     */
    public function __invoke($request) {
        [$moduleName, $controllerName,] = $request->handler();
        if (!$moduleName || !$controllerName) {
            return false;
        }

        $module = $this->moduleIndex->module($moduleName);

        $this->registerModuleClassLoader($module);

        // @TODO: Register simple autoloader, which must try to load the class using simple scheme, then call Composer's autoloader in case of failure.
        $classWithoutModuleNsPrefix = $this->controllerClassWithoutModuleNs($controllerName);
        $handler = $this->mkInstance($module, $classWithoutModuleNsPrefix);
        $request['handlerFn'] = $handler;
        return $handler;
    }

    /**
     * @param Module $module
     * @param string $classWithoutModuleNsPrefix
     * @return array|false
     */
    public function classFilePath(Module $module, string $classWithoutModuleNsPrefix) {
        $relClassFilePath = \str_replace('\\', '/', $classWithoutModuleNsPrefix) . '.php';
        foreach ($module['namespace'] as $namespace => $nsDirPath) {
            $class = $namespace . '\\' . $classWithoutModuleNsPrefix;
            $classFilePath = $module['path']['dirPath'] . '/' . $nsDirPath . '/' .  $relClassFilePath;
            if (\is_file($classFilePath)) {
                return [$class, $classFilePath];
            }
        }
        return false;
    }

    /**
     * @param Module $module
     * @param string $classWithoutModuleNsPrefix Class suffix like Http\IndexController, which will added to module's namespaces.
     * @return \object|false
     */
    public function mkInstance(Module $module, string $classWithoutModuleNsPrefix) {
        $classFilePath = $this->classFilePath($module, $classWithoutModuleNsPrefix);
        if (false !== $classFilePath) {
            [$class, $filePath] = $classFilePath;
            if (!\class_exists($class, false)) {
                requireFile($filePath, true);
            }
            $instance = new $class();
            if ($instance instanceof IHasServiceManager) {
                $instance->setServiceManager($this->serviceManager);
            }
            return $instance;
        }
        return false;
    }

    protected function registerModuleClassLoader(Module $module): void {
        $moduleName = $module->name();
        if (!isset($this->registeredModules[$moduleName])) {
            /** @noinspection PhpIncludeInspection */
            require_once $module->autoloadFilePath();
            $this->registeredModules[$moduleName] = true;
        }
    }

    abstract protected function controllerClassWithoutModuleNs(string $controllerName): string;
}
