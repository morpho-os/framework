<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Routing;

use Morpho\App\Web\Controller;
use ReflectionClass;
use function Morpho\Base\endsWith;
use Morpho\Base\IFn;
use Morpho\Code\Reflection\ClassTypeReflection;
use Morpho\Code\Reflection\FileReflection;
use const Morpho\App\CONTROLLER_SUFFIX;

class ActionMetaProvider implements IFn {
    protected array $baseControllerClasses = [Controller::class];

    private ?array $ignoredMethods = null;

    private const ACTION_SUFFIX = 'Action';

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
            if ($this->shouldBeSkipped($rClass)) {
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

    private function shouldBeSkipped(ClassTypeReflection $rClass): bool {
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
        $rClass = new ReflectionClass($controllerMeta['class']);
        $ignoredMethods = $this->ignoredMethods();
        foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $rMethod) {
            $method = $rMethod->getName();
            if (\in_array($method, $ignoredMethods)) {
                continue;
            }
            if (endsWith(\strtolower($method), \strtolower(self::ACTION_SUFFIX))) {
                $action = \substr($method, 0, -\strlen(self::ACTION_SUFFIX));
                $actionsMeta[$action] = [
                    'module' => $controllerMeta['module'],
                    'action' => $action,
                    'class' => $controllerMeta['class'],
                    'method' => $method,
                    'filePath' => $controllerMeta['filePath'],
                ];
                $docComment = $rMethod->getDocComment();
                if ($docComment) {
                    $actionsMeta[$action]['docComment'] = $docComment;
                }
            }
        }
        return \array_values($actionsMeta);
    }

    private function ignoredMethods(): array {
        if (null === $this->ignoredMethods) {
            $ignoredMethods = [];
            foreach ($this->baseControllerClasses as $class) {
                $rClass = new ReflectionClass($class);
                foreach ($rClass->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $rMethod) {
                    $method = $rMethod->getName();
                    if (endsWith(\strtolower($method), \strtolower(self::ACTION_SUFFIX))) {
                        $ignoredMethods[] = $method;
                    }
                }
            }
            $this->ignoredMethods = $ignoredMethods;
        }
        return $this->ignoredMethods;
    }
}
