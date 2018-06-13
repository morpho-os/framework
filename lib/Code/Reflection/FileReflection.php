<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Reflection;

use function Morpho\Base\init;
use function Morpho\Code\Parsing\isClassType;
use function Morpho\Code\Parsing\parseFile;
use PhpParser\Node\Stmt\Function_ as FunctionStmt;
use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class FileReflection {
    private $filePath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function filePath(): string {
        return $this->filePath;
    }

    /**
     * @return \Traversable|NamespaceReflection[]
     */
    public function namespaces(): iterable {
        $ast = parseFile($this->filePath);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $ast = $traverser->traverse($ast);
        $globalClassTypes = $globalFunctions = [];
        foreach ($ast as $node) {
            if ($node instanceof NamespaceStmt) {
                yield new NamespaceReflection(['filePath' => $this->filePath, 'node' => $node, 'name' => $node->name->toString()]);
            } elseif (isClassType($node)) {
                $globalClassTypes[] = $node;
            } elseif ($node instanceof FunctionStmt) {
                $globalFunctions[] = $node;
            }
        }
        if (\count($globalClassTypes) || \count($globalFunctions)) {
            yield new GlobalNamespaceReflection(['filePath' => $this->filePath, 'classTypes' => $globalClassTypes, 'functions' => $globalFunctions]);
        }
    }

    /**
     * @return \Traversable|ClassTypeReflection[]
     */
    public function classes(): iterable {
        return $this->filterClassTypes(function (ClassTypeReflection $rClass) {
            return !$rClass->isTrait() && !$rClass->isInterface();
        });
    }

    /**
     * @return \Traversable|ClassTypeReflection[]
     */
    public function traits(): iterable {
        return $this->filterClassTypes(function (ClassTypeReflection $rClass) {
            return $rClass->isTrait();
        });
    }

    /**
     * @return \Traversable|ClassTypeReflection[]
     */
    public function interfaces(): iterable {
        return $this->filterClassTypes(function (ClassTypeReflection $rClass) {
            return $rClass->isInterface();
        });
    }


/*    private function nodeName(Node $node): string {
        if ($node->name instanceof Identifier) {
            return $node->name->name;
        }
        return $node->name;
    }*/

    /**
     * @param \Closure $filter
     * @return iterable|ClassTypeReflection[]
     */
    private function filterClassTypes(\Closure $filter): iterable {
        foreach ($this->namespaces() as $rNamespace) {
            /** @var $rNamespace NamespaceReflection */
            foreach ($rNamespace->classTypes() as $classType) {
                if ($filter($classType)) {
                    yield $classType;
                }
            }
        }
    }
}

interface INamedType {
    public function name(): ?string;

    public function filePath(): string;
}

class NamespaceReflection implements INamedType {
    protected $context;

    public function __construct(array $context) {
        $this->context = $context;
    }

    public function filePath(): string {
        return $this->context['filePath'];
    }

    public function name(): ?string {
        return $this->context['name'];
    }

    /**
     * @return iterable|ClassTypeReflection[]
     */
    public function classTypes(): iterable {
        foreach ($this->context['node']->stmts as $node) {
            if (isClassType($node)) {
                yield new ClassTypeReflection(['node' => $node, 'filePath' => $this->context['filePath'], 'name' => $node->namespacedName->toString()]);
            }
        }
    }

    /**
     * @return iterable|FunctionReflection[]
     */
    public function functions(): iterable {
        foreach ($this->context['node']->stmts as $node) {
            if ($node instanceof FunctionStmt) {
                yield new FunctionReflection(['node' => $node, 'filePath' => $this->context['filePath'], 'name' => $node->namespacedName->toString()]);
            }
        }
    }
}

class GlobalNamespaceReflection extends NamespaceReflection {
    public function name(): ?string {
        return null;
    }

    public function classTypes(): iterable {
        foreach ($this->context['classTypes'] as $node) {
            yield new ClassTypeReflection(['node' => $node, 'filePath' => $this->context['filePath'], 'name' => $node->namespacedName->toString()]);
        }
    }

    /**
     * @return iterable|FunctionReflection[]
     */
    public function functions(): iterable {
        foreach ($this->context['functions'] as $node) {
            yield new FunctionReflection(['node' => $node, 'filePath' => $this->context['filePath'], 'name' => $node->namespacedName->toString()]);
        }
    }
}

class ClassTypeReflection implements INamedType {
    private $context;

    public function __construct(array $context) {
        $this->context = $context;
    }

    public function name(): string {
        return $this->context['name'];
    }

    public function filePath(): string {
        return $this->context['filePath'];
    }

    public function isTrait(): bool {
        return $this->context['node'] instanceof TraitStmt;
    }

    public function isInterface(): bool {
        return $this->context['node'] instanceof InterfaceStmt;
    }

    public function nameOfNamespace(): string {
        return init($this->context['node']->namespacedName->toString(), '\\');
    }
}


class FunctionReflection implements INamedType {
    private $context;

    public function __construct(array $context) {
        $this->context = $context;
    }

    public function name(): ?string {
        return $this->context['name'];
    }

    public function filePath(): string {
        return $this->context['filePath'];
    }
}
