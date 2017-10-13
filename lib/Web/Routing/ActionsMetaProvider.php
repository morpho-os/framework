<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use Morpho\Base\ClassNotFoundException;
use function Morpho\Base\{
    endsWith, last
};
use Morpho\Code\ClassTypeDiscoverer;
use const Morpho\Core\ACTION_SUFFIX;
use const Morpho\Core\CONTROLLER_SUFFIX;
use Morpho\Fs\Directory;
use Morpho\Web\ModuleFs;
use Morpho\Web\ModuleManager;

class ActionsMetaProvider implements \IteratorAggregate {
    private $moduleManager;

    protected $baseControllerClasses = [
        'Morpho\\Core\\Controller',
        'Morpho\\Web\\Controller',
    ];

    private $ignoredMethods;
    private $controllerFilePathsProvider;

    public function __construct($moduleManager) {
        $this->moduleManager = $moduleManager;
    }

    public function getIterator() {
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        $controllerFilePathsProvider = $this->controllerFilePathsProvider();
        foreach ($this->moduleManager->enabledModuleNames() as $moduleName) {
            foreach ($controllerFilePathsProvider($moduleName) as $controllerFilePath) {
                $classTypes = $classTypeDiscoverer->classTypesDefinedInFile($controllerFilePath);
                foreach (array_keys($classTypes) as $classType) {
                    if (endsWith($classType, CONTROLLER_SUFFIX)) {
                        yield from $this->collectActionsMeta($classType, $moduleName, $this->classToControllerName($classType));
                    }
                }
            }
        }
    }

    public function setControllerFilePathsProvider($provider) {
        $this->controllerFilePathsProvider = $provider;
    }

    public function controllerFilePathsProvider() {
        if (null === $this->controllerFilePathsProvider) {
            $this->controllerFilePathsProvider = new ControllerFilePathsProvider($this->moduleManager);
        }
        return $this->controllerFilePathsProvider;
    }

    protected function collectActionsMeta(string $controllerClass, string $moduleName, string $controllerName) {
        $actionsMeta = [];
        if (!class_exists($controllerClass)) {
            $this->moduleManager->fs()->registerModuleAutoloader($moduleName);
            if (!class_exists($controllerClass)) {
                throw new ClassNotFoundException("Unable to load the class '$controllerClass' for the module '$moduleName', ensure that the class is defined");
            }
        }
        $rClass = new \ReflectionClass($controllerClass);
        $ignoredMethods = $this->ignoredMethods();
        foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $rMethod) {
            $method = $rMethod->getName();
            if (in_array($method, $ignoredMethods)) {
                continue;
            }
            if (endsWith(strtolower($method), strtolower(ACTION_SUFFIX))) {
                $action = substr($method, 0, -strlen(ACTION_SUFFIX));
                $actionsMeta[$action] = ['module' => $moduleName, 'controller' => $controllerName, 'action' => $action, 'class' => $controllerClass];
                $docComment = $rMethod->getDocComment();
                if ($docComment) {
                    $actionsMeta[$action]['docComment'] = $docComment;
                }
            }
        }
        return array_values($actionsMeta);
    }

    private function classToControllerName(string $class) {
        $controllerName = last($class, '\\');
        $suffixLength = strlen(CONTROLLER_SUFFIX);
        return substr($controllerName, 0, -$suffixLength);
    }

    private function ignoredMethods() {
        if (null === $this->ignoredMethods) {
            $ignoredMethods = [];
            foreach ($this->baseControllerClasses as $class) {
                $rClass = new \ReflectionClass($class);
                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $rMethod) {
                    $method = $rMethod->getName();
                    if (endsWith(strtolower($method), strtolower(ACTION_SUFFIX))) {
                        $ignoredMethods[] = $method;
                    }
                }
            }
            $this->ignoredMethods = $ignoredMethods;
        }
        return $this->ignoredMethods;
    }
}

class ControllerFilePathsProvider {
    private $moduleManager;

    public function __construct(ModuleManager $moduleManager) {
        $this->moduleManager = $moduleManager;
    }

    public function __invoke(string $moduleName): iterable {
        $moduleDirPath = $this->moduleManager->fs()->moduleDirPath($moduleName);
        $controllerDirPath = (new ModuleFs($moduleDirPath))->controllerDirPath();
        if (!is_dir($controllerDirPath)) {
            return [];
        }
        return Directory::filePaths($controllerDirPath, '~.Controller\.php$~s', ['recursive' => true]);
    }
}