<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\IFn;
use Morpho\Code\Reflection\FileReflection;
use ReflectionClass;
use ReflectionMethod;
use function Morpho\Base\endsWith;
use function Morpho\Base\it;

class ActionMetaProvider implements IFn {
    protected $controllerFilter;
    protected $actionFilter;

    public function __construct(callable $controllerFilter = null, callable $actionFilter = null) {
        $this->controllerFilter = $controllerFilter;
        $this->actionFilter = $actionFilter;
    }

    /**
     * @param iterable|\Closure $modules Iterable over ServerModule or \Closure returning \Generator
     * @return iterable Iterable over action meta
     * @throws \ReflectionException
     */
    public function __invoke($modules): iterable {
        $controllerFilter = $this->controllerFilter();
        $actionFilter = $this->actionFilter();
        foreach (it($modules) as $module) {
            /** @var \Morpho\App\ServerModule $module */
            foreach ($module->controllerFilePaths() as $controllerFilePath) {
                foreach ((new FileReflection($controllerFilePath))->classes() as $rClass) {
                    if (!$controllerFilter($rClass)) {
                        continue;
                    }
                    $controllerMeta = [
                        'module' => $module->name(),
                        'filePath' => $controllerFilePath,
                        'class' => $rClass->getName(),
                    ];
                    /** @noinspection PhpIncludeInspection */
                    require_once $controllerFilePath;
                    $actionsMeta = [];
                    foreach ((new ReflectionClass($controllerMeta['class']))->getMethods(ReflectionMethod::IS_PUBLIC) as $rMethod) {
                        if (!$actionFilter($rMethod)) {
                            continue;
                        }
                        $method = $rMethod->getName();
                        $actionsMeta[$method] = [
                            'module' => $controllerMeta['module'],
                            'class' => $controllerMeta['class'],
                            'filePath' => $controllerMeta['filePath'],
                            'method' => $method,
                        ];
                        $docComment = $rMethod->getDocComment();
                        if ($docComment) {
                            $actionsMeta[$method]['docComment'] = $docComment;
                        }
                    }
                    yield from \array_values($actionsMeta);
                }
            }
        }
    }

    public function setControllerFilter(callable $controllerFilter) {
        $this->controllerFilter = $controllerFilter;
        return $this;
    }

    public function controllerFilter(): callable {
        if (null === $this->controllerFilter) {
            $this->controllerFilter = function (\ReflectionClass $rClass): bool {
                if ($rClass->isAbstract()) {
                    return false;
                }
                return endsWith($rClass->getName(), CONTROLLER_SUFFIX);
            };
        }
        return $this->controllerFilter;
    }

    public function setActionFilter(callable $actionFilter) {
        $this->actionFilter = $actionFilter;
        return $this;
    }

    protected function actionFilter(): callable {
        if (null === $this->actionFilter) {
            $baseControllerClasses = [\Morpho\App\Cli\Controller::class, \Morpho\App\Web\Controller::class];
            $ignoredMethods = [];
            foreach ($baseControllerClasses as $baseControllerClass) {
                foreach ((new ReflectionClass($baseControllerClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $rMethod) {
                    $ignoredMethods[] = $rMethod->getName();
                }
            }
            $this->actionFilter = function (\ReflectionMethod $rMethod) use ($ignoredMethods): bool {
                $method = $rMethod->getName();
                if (\in_array($method, $ignoredMethods)) {
                    return false;
                }
                if (!preg_match('~^[a-z]~si', $method)) {
                    return false;
                }
                $docComment = $rMethod->getDocComment();
                if ($docComment) {
                    if (false !== strpos($docComment, '@notAction')) {
                        return false;
                    }
                }
                return true;
            };

        }
        return $this->actionFilter;
    }
}