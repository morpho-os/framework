<?php
namespace Morpho\Code;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt;

class ReflectionFile {
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function filePath() {
        return $this->filePath;
    }

    public function namespaces(): iterable {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $stmts = $parser->parse(file_get_contents($this->filePath));

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver());

        $stmts = $traverser->traverse($stmts);

        $globalClassTypes = $globalFunctions = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Stmt\Namespace_) {
                yield new ReflectionNamespace($this->filePath(), $stmt->name->toString(), $this->classTypes($stmt), $this->functions($stmt), false);
            } elseif ($this->isClassType($stmt)) {
                $globalClassTypes[] = $stmt->name;
            } elseif ($this->isFunction($stmt)) {
                $globalFunctions[] = $stmt->name;
            }
        }
        if (count($globalClassTypes) || count($globalFunctions)) {
            yield new ReflectionNamespace($this->filePath(), null, $globalClassTypes, $globalFunctions, true);
        }
    }

    private function classTypes($nsNode) {
        foreach ($nsNode->stmts as $node) {
            if ($this->isClassType($node)) {
                yield $node->namespacedName->toString();
            }
        }
    }

    private function functions($nsNode) {
        foreach ($nsNode->stmts as $node) {
            if ($this->isFunction($node)) {
                yield $node->namespacedName->toString();
            }
        }
    }

    private function isFunction($node) {
        return $node instanceof Stmt\Function_;
    }

    private function isClassType($node) {
        return $node instanceof Stmt\Class_
            || $node instanceof Stmt\Trait_
            || $node instanceof Stmt\Interface_;
    }
}

