<?php
namespace Morpho\Code;

use Morpho\Code\ClassTypeDiscoverer\IDiscoverStrategy;
use Morpho\Code\ClassTypeDiscoverer\TokenStrategy;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPParser\Node\Expr\Instanceof_;
use PHPParser\Node\Expr\New_;
use PHPParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPParser\Node\Name\FullyQualified;
use PHPParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPParser\Node\Stmt\Interface_;
use PHPParser\Node\Stmt\TraitUse;
use PHPParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;

class ClassTypeDiscoverer {
    private $discoverStrategy;

    const PHP_FILES_REG_EXP = '~\.php$~si';

    public function classTypesDefinedInDir($dirPaths, string $regExp = null, array $options = []): array {
        if (!$regExp) {
            $regExp = self::PHP_FILES_REG_EXP;
        }
        $filePaths = Directory::listFiles($dirPaths, $regExp, $options);
        $map = [];
        $discoverStrategy = $this->getDiscoverStrategy();
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
        foreach ($this->getDiscoverStrategy()->classTypesDefinedInFile($filePath) as $classType) {
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
    
    public static function fileDependsFromClassTypes(string $filePath): array {
        $parser = new Parser(new Lexer());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $depsCollector = new ClassTypeDepsCollector();
        $traverser->addVisitor($depsCollector);
        $statements = $parser->parse(File::read($filePath));
        $traverser->traverse($statements);
        return $depsCollector->classTypes();
    }
}

class ClassTypeDepsCollector extends NodeVisitorAbstract {
    protected $classTypes = [];

    public function classTypes(): array {
        return $this->classTypes;
    }

    public function leaveNode(Node $node) {
        if ($node instanceof Function_ || $node instanceof ClassMethod) {
            if ($node->returnType && $node->returnType instanceof FullyQualified) {
                $this->classTypes[] = implode('\\', $node->returnType->parts);
            }
        } elseif ($node instanceof Class_) {
            if (isset($node->extends)) {
                $this->classTypes[] = $node->extends->toString();
            }
            if (isset($node->implements)) {
                foreach ($node->implements as $nodeName) {
                    $this->classTypes[] = $nodeName->toString();
                }
            }
        } elseif ($node instanceof Interface_) {
            foreach ($node->extends as $nodeName) {
                $this->classTypes[] = $nodeName->toString();
            }
        } elseif ($node instanceof TryCatch) {
            foreach ($node->catches as $catchStmt) {
                $this->classTypes[] = implode('\\', $catchStmt->type->parts);
            }
        } elseif ($node instanceof TraitUse) {
            foreach ($node->traits as $nodeName) {
                $this->classTypes[] = $nodeName->toString();
            }
        } elseif ($node instanceof New_ && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        } elseif ($node instanceof Param && $node->type instanceof FullyQualified) {
            $this->classTypes[] = implode('\\', $node->type->parts);
        } elseif ($node instanceof Instanceof_ && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        } elseif ($node instanceof StaticPropertyFetch) {
            $this->classTypes[] = $node->class->toString();
        } elseif ($node instanceof StaticCall && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        } elseif ($node instanceof ClassConstFetch) {
            $this->classTypes[] = $node->class->toString();
        }
    }

    public function beforeTraverse(array $nodes) {
        parent::beforeTraverse($nodes);
        $this->classTypes = [];
    }

    public function afterTraverse(array $nodes) {
        parent::afterTraverse($nodes);
        $this->classTypes = array_unique($this->classTypes);
    }
}

