<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\ClassTypeDiscoverer;

use Morpho\Code\ClassTypeDepsCollector;
use Morpho\Code\StdClassTypeFilter;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;

class ClassTypeDiscoverer {
    private $discoverStrategy;

    public static function definedClassTypes(): array {
        return array_merge(
            self::definedClasses(),
            get_declared_interfaces(),
            get_declared_traits()
        );
    }
    
    public static function definedClasses(): array {
        return array_filter(get_declared_classes(), function ($class) {
            // Skip anonymous classes.
            return 'class@anonymous' !== substr($class, 0, 15);
        });
    }

    public function classTypesDefinedInDir($dirPaths, string $regExp = null, array $options = null): array {
        $options = (array) $options + ['recursive' => true];
        $filePaths = Directory::filePaths($dirPaths, $regExp ?: Directory::PHP_FILES_RE, $options);
        $map = [];
        $discoverStrategy = $this->discoverStrategy();
        foreach ($filePaths as $filePath) {
            foreach ($discoverStrategy->classTypesDefinedInFile($filePath) as $classType) {
                if (isset($map[$classType])) {
                    throw new \RuntimeException("Cannot redeclare the class|interface|trait '$classType' in '$filePath'");
                }
                $map[$classType] = $filePath;
            }
        }
        return $map;
    }

    public function classTypesDefinedInFile(string $filePath): array {
        $map = [];
        foreach ($this->discoverStrategy()->classTypesDefinedInFile($filePath) as $classType) {
            $map[$classType] = $filePath;
        }
        return $map;
    }

    public function setDiscoverStrategy(IDiscoverStrategy $strategy): self {
        $this->discoverStrategy = $strategy;
        return $this;
    }

    public function discoverStrategy(): IDiscoverStrategy {
        if (null === $this->discoverStrategy) {
            $this->discoverStrategy = new TokenStrategy();
        }
        return $this->discoverStrategy;
    }

    public static function classTypeFilePath(string $classType): string {
        return (new \ReflectionClass($classType))->getFileName();
    }
    
    public static function fileDependsFromClassTypes(string $filePath, bool $excludeStdClasses = true): array {
        $phpCode = File::read($filePath);

        $parser = new Parser(new Lexer());

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $statements = $traverser->traverse($parser->parse($phpCode));

        $depsCollector = new ClassTypeDepsCollector();
        $traverser->addVisitor($depsCollector);
        $traverser->traverse($statements);
        return $excludeStdClasses
            ? (new StdClassTypeFilter())->__invoke($depsCollector->classTypes())
            : $depsCollector->classTypes();
    }
}

