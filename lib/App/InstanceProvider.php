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

class InstanceProvider implements IFn {
    /**
     * @var ModuleIndex
     */
    protected $moduleIndex;

    /**
     * @var array
     */
    private $registeredModules = [];

    /**
     * @var IServiceManager
     */
    private $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->moduleIndex = $serviceManager['moduleIndex'];
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

        $moduleMeta = $this->moduleIndex->moduleMeta($moduleName);

        $this->registerModuleClassLoader($moduleMeta, $moduleName);

        // @TODO: Register simple autoloader, which must try to load the class using simple scheme, then call Composer's autoloader in case of failure.
        $classWithoutModuleNsPrefix = $this->controllerClassWithoutModuleNs($controllerName);
        $handler = $this->mkInstance($moduleMeta, $classWithoutModuleNsPrefix);
        $request['handlerFn'] = $handler;
        return $handler;
    }

    /**
     * @param ModuleMeta $moduleMeta
     * @param string $classWithoutModuleNsPrefix
     * @return array|false
     */
    public function classFilePath(ModuleMeta $moduleMeta, string $classWithoutModuleNsPrefix) {
        foreach ($moduleMeta['namespace'] as $namespace => $nsDirPath) {
            $class = $namespace . '\\' . $classWithoutModuleNsPrefix;
            $classFilePath = $moduleMeta['path']['dirPath'] . '/' . $nsDirPath . '/' . \str_replace('\\', '/', $classWithoutModuleNsPrefix) . '.php';
            if (\is_file($classFilePath)) {
                return [$class, $classFilePath];
            }
        }
        return false;
    }

    /**
     * @param ModuleMeta $moduleMeta
     * @param string $classWithoutModuleNsPrefix Class suffix like Web\IndexController, which will added to module's namespaces.
     * @return \object|false
     */
    public function mkInstance(ModuleMeta $moduleMeta, string $classWithoutModuleNsPrefix) {
        $classPath = $this->classFilePath($moduleMeta, $classWithoutModuleNsPrefix);
        if (false !== $classPath) {
            [$class, $filePath] = $classPath;
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

    protected function registerModuleClassLoader(ModuleMeta $moduleMeta, string $moduleName): void {
        if (!isset($this->registeredModules[$moduleName])) {
            /** @noinspection PhpIncludeInspection */
            require_once $moduleMeta->autoloadFilePath();
            $this->registeredModules[$moduleName] = true;
        }
    }

    protected function controllerClassWithoutModuleNs($controllerName): string {
        $isCli = PHP_SAPI == 'cli';
        $classWithoutModuleNsPrefix = 'App' . '\\' . ($isCli ? 'Cli' : 'Web') . '\\' . $controllerName . CONTROLLER_SUFFIX;
        return $classWithoutModuleNsPrefix;
    }
}
