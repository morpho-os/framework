<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use Morpho\App\Web\Controller;
use ReflectionClass;
use ReflectionMethod;
use function Morpho\Base\endsWith;
use Morpho\Base\IFn;
use Morpho\Code\Reflection\ClassTypeReflection;
use Morpho\Code\Reflection\FileReflection;
use const Morpho\App\CONTROLLER_SUFFIX;

class ActionMetaProvider implements IFn {
    protected array $baseControllerClasses = [Controller::class];

    /**
     * @param iterable $controllerFileMetas
     * @return iterable
     */
    public function __invoke($controllerFileMetas): iterable {
        foreach ($controllerFileMetas as $controllerFileMeta) {
            foreach ($this->controllerMeta($controllerFileMeta) as $controllerMeta) {
                yield from $this->actionMeta($controllerMeta);
            }
        }
    }

    private function controllerMeta(array $controllerFileMeta): iterable {
        require_once $controllerFileMeta['filePath'];
        foreach ((new FileReflection($controllerFileMeta['filePath']))->classes() as $rClass) {
            if ($this->controllerMustBeSkipped($rClass)) {
                continue;
            }
            $class = $rClass->getName();
            yield [
                'module' => $controllerFileMeta['module'],
                'class' => $class,
                'filePath' => $controllerFileMeta['filePath'],
            ];
        }
    }

    private function controllerMustBeSkipped(ClassTypeReflection $rClass): bool {
        if ($rClass->isAbstract()) {
            return true;
        }
        if (!endsWith($rClass->getName(), CONTROLLER_SUFFIX)) {
            return true;
        }
        $docComments = $rClass->getDocComment();
        return false !== $docComments && (bool) preg_match('~\s*@noRoutes\s*~si', $docComments);
    }

    private function actionMeta(array $controllerMeta): array {
        $actionsMeta = [];
        foreach ($this->actionMethods($controllerMeta['class']) as $rMethod) {
            $method = $rMethod->getName();
            $actionsMeta[$method] = [
                'module' => $controllerMeta['module'],
                'class' => $controllerMeta['class'],
                'method' => $method,
                'filePath' => $controllerMeta['filePath'],
            ];
            $docComment = $rMethod->getDocComment();
            if ($docComment) {
                $actionsMeta[$method]['docComment'] = $docComment;
            }
        }
        return \array_values($actionsMeta);
    }

    private function actionMethods(string $controllerClass): array {
        $ignoredMethods = [];
        foreach ($this->baseControllerClasses as $baseControllerClass) {
            foreach ((new ReflectionClass($baseControllerClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $rMethod) {
                $ignoredMethods[] = $rMethod->getName();
            }
        }
        $actionMethods = [];
        foreach ((new ReflectionClass($controllerClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $rMethod) {
            $method = $rMethod->getName();
            if (\in_array($method, $ignoredMethods)) {
                continue;
            }
            $docComment = $rMethod->getDocComment();
            if ($docComment) {
                if (false !== strpos($docComment, '@@notAction')) {
                    continue;
                }
            }
            $actionMethods[] = $rMethod;
        }
        return $actionMethods;
    }
}
