<?php
namespace Morpho\Code;

use Morpho\Code\ClassTypeDiscoverer\IDiscoverStrategy;
use Morpho\Code\ClassTypeDiscoverer\TokenStrategy;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;

class ClassTypeDiscoverer {
    private $discoverStrategy;

    const PHP_FILES_REG_EXP = '~\.php$~si';
    
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

    public function definedClassTypesInDir($dirPaths, string $regExp = null, array $options = []): array {
        if (!$regExp) {
            $regExp = self::PHP_FILES_REG_EXP;
        }
        $filePaths = Directory::listFiles($dirPaths, $regExp, $options);
        $map = [];
        $discoverStrategy = $this->getDiscoverStrategy();
        foreach ($filePaths as $filePath) {
            foreach ($discoverStrategy->definedClassTypesInFile($filePath) as $classType) {
                if (isset($map[$classType])) {
                    throw new \RuntimeException("Cannot redeclare the class|interface|trait '$classType' in '$filePath'");
                }
                $map[$classType] = $filePath;
            }
        }
        return $map;
    }

    public function definedClassTypesInFile(string $filePath): array {
        $map = [];
        foreach ($this->getDiscoverStrategy()->definedClassTypesInFile($filePath) as $classType) {
            $map[$classType] = $filePath;
        }
        return $map;
    }

    public function setDiscoverStrategy(IDiscoverStrategy $strategy): self {
        $this->discoverStrategy = $strategy;
        return $this;
    }

    public function getDiscoverStrategy(): IDiscoverStrategy {
        if (null === $this->discoverStrategy) {
            $this->discoverStrategy = new TokenStrategy();
        }
        return $this->discoverStrategy;
    }

    /**
     * @throws \ReflectionException
     */
    public static function classTypeFilePath(string $classType): string {
        return (new \ReflectionClass($classType))->getFileName();
    }
    
    public static function fileDependsFromClassTypes(string $filePath, bool $excludeStdClasses = true): array {
        $parser = new Parser(new Lexer());

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $depsCollector = new ClassTypeDepsCollector();
        $statements = $traverser->traverse($parser->parse(File::read($filePath)));

        $traverser->addVisitor($depsCollector);
        $traverser->traverse($statements);
        return $excludeStdClasses
            ? (new StdClassTypeFilter())->filter($depsCollector->classTypes())
            : $depsCollector->classTypes();
    }
}

