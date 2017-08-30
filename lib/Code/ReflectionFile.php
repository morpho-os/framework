<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\Function_ as FunctionStmt;
use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
use PhpParser\Node\Stmt\Namespace_ as NamespaceStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class ReflectionFile {
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function filePath(): string {
        return $this->filePath;
    }

    public function namespaces(): iterable {
        $stmts = parseFile($this->filePath);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver());

        $stmts = $traverser->traverse($stmts);

        $globalClassTypes = $globalFunctions = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof NamespaceStmt) {
                yield new ReflectionNamespace($this->filePath(), $stmt->name->toString(), $this->classTypes($stmt), $this->functions($stmt), false);
            } elseif ($this->isClassType($stmt)) {
                $globalClassTypes[] = $this->nodeName($stmt);
            } elseif ($this->isFunction($stmt)) {
                $globalFunctions[] = $this->nodeName($stmt);
            }
        }
        if (count($globalClassTypes) || count($globalFunctions)) {
            yield new ReflectionNamespace($this->filePath(), null, $globalClassTypes, $globalFunctions, true);
        }
    }

    private function classTypes(NamespaceStmt $nsNode): iterable {
        foreach ($nsNode->stmts as $node) {
            if ($this->isClassType($node)) {
                yield $node->namespacedName->toString();
            }
        }
    }

    private function functions(NamespaceStmt $nsNode): iterable {
        foreach ($nsNode->stmts as $node) {
            if ($this->isFunction($node)) {
                yield $node->namespacedName->toString();
            }
        }
    }

    private function isFunction(Node $node): bool {
        return $node instanceof FunctionStmt;
    }

    private function isClassType(Node $node): bool {
        return $node instanceof ClassStmt
            || $node instanceof TraitStmt
            || $node instanceof InterfaceStmt;
    }

    private function nodeName(Node $node): string {
        if ($node->name instanceof Identifier) {
            return $node->name->name;
        }
        return $node->name;
    }
}