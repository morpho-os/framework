<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Routing;

use function Morpho\Base\{
    endsWith, last
};
use Morpho\Base\IFn;
use Morpho\Code\Reflection\ReflectionFile;
use const Morpho\Core\CONTROLLER_SUFFIX;
use const Morpho\Core\ACTION_SUFFIX;

class ActionMetaProvider implements IFn {
    protected $baseControllerClasses = [
        //'Morpho\\Core\\Controller',
        'Morpho\\Web\\Controller',
    ];

    private $ignoredMethods;

    public function __invoke($controllerFileMetas): iterable {
        foreach ($controllerFileMetas as $controllerFileMeta) {
            foreach ($this->controllerMeta($controllerFileMeta) as $controllerMeta) {
                yield from $this->actionMeta($controllerMeta);
            }
        }
    }

    private function controllerMeta(array $controllerFileMeta): iterable {
        require_once $controllerFileMeta['filePath'];
        foreach ((new ReflectionFile($controllerFileMeta['filePath']))->classes() as $rClass) {
            $class = $rClass->getName();
            if (!endsWith($class, CONTROLLER_SUFFIX)) {
                continue;
            }
            $controllerMeta = [
                'filePath' => $controllerFileMeta['filePath'],
                'module' => $controllerFileMeta['module'],
                'class' => $class,
                'controller' => $this->classToControllerName($class),
            ];
            yield $controllerMeta;
        }
    }

    private function actionMeta(array $controllerMeta): array {
        $actionsMeta = [];
        $rClass = new \ReflectionClass($controllerMeta['class']);
        $ignoredMethods = $this->ignoredMethods();
        foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $rMethod) {
            $method = $rMethod->getName();
            if (in_array($method, $ignoredMethods)) {
                continue;
            }
            if (endsWith(strtolower($method), strtolower(ACTION_SUFFIX))) {
                $action = substr($method, 0, -strlen(ACTION_SUFFIX));
                $actionsMeta[$action] = [
                    'module' => $controllerMeta['module'],
                    'controller' => $controllerMeta['controller'],
                    'action' => $action,
                    'class' => $controllerMeta['class'],
                    'filePath' => $controllerMeta['filePath'],
                ];
                $docComment = $rMethod->getDocComment();
                if ($docComment) {
                    $actionsMeta[$action]['docComment'] = $docComment;
                }
            }
        }
        return array_values($actionsMeta);
    }

    private function ignoredMethods(): array {
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

    private function classToControllerName(string $class): string {
        $controllerName = last($class, '\\');
        $suffixLength = strlen(CONTROLLER_SUFFIX);
        return substr($controllerName, 0, -$suffixLength);
    }
}