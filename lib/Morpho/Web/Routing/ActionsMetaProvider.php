<?php
namespace Morpho\Web\Routing;

use Morpho\Base\ClassNotFoundException;
use function Morpho\Base\{
    endsWith, last
};
use Morpho\Code\ClassTypeDiscoverer;
use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

class ActionsMetaProvider implements \IteratorAggregate, IServiceManagerAware {
    protected $moduleManager;

    protected $serviceManager;

    protected $baseControllerClasses = [
        'Morpho\\Core\\Controller',
        'Morpho\\Web\\Controller',
    ];

    private $ignoredMethods;

    public function setModuleManager($moduleManager) {
        $this->moduleManager = $moduleManager;
    }

    public function getIterator() {
        $moduleManager = $this->serviceManager->get('moduleManager');
        $moduleFs = $moduleManager->getModuleFs();
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        foreach ($moduleManager->enabledModuleNames() as $moduleName) {
            $moduleFs->registerModuleAutoloader($moduleName);
            foreach ($moduleFs->getModuleControllerFilePaths($moduleName) as $controllerFilePath) {
                $classTypes = $classTypeDiscoverer->definedClassTypesInFile($controllerFilePath);
                foreach (array_keys($classTypes) as $classType) {
                    if (endsWith($classType, CONTROLLER_SUFFIX)) {
                        yield from $this->collectActionsMeta($classType, $moduleName, $this->classToControllerName($classType));
                    }
                }
            }
        }
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function collectActionsMeta(string $controllerClass, string $moduleName, string $controllerName) {
        $actionsMeta = [];
        if (!class_exists($controllerClass)) {
            throw new ClassNotFoundException("Unable to load the class '$controllerClass' for the module '$moduleName', ensure that the class is defined");
        }
        $rClass = new \ReflectionClass($controllerClass);
        $ignoredMethods = $this->getIgnoredMethods();
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

    private function getIgnoredMethods() {
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