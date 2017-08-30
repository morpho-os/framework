<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Base\Must;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class TreeRewriter extends NodeVisitorAbstract {
    /**
     * @var MetaCollector
     */
    private $meta;

    public function beforeTraverse(array $nodes) {
        $this->collectMeta($nodes);
    }

    public function enterNode(Node $node) {
    }

    public function leaveNode(Node $node) {
        if ($node instanceof Stmt\Class_) {
            $className = (string)$node->name;
            if (!$this->meta->hasConstructor($className) && $this->meta->hasProperties($className)) {
                $stmts = &$node->stmts;
                $assignStmts = [];
                foreach ($this->meta->properties($className) as $prop) {
                    $assignStmts[] = new Stmt\Expression(
                        new Expr\Assign(
                            new Expr\PropertyFetch(new Expr\Variable('this'), $prop->name),
                            $prop->default === null ? new Expr\ConstFetch(new Node\Name('null')) : $prop->default
                        )
                    );
                }
                $stmts[] = new Stmt\ClassMethod(new Node\Identifier('__construct'), ['stmts' => $assignStmts, 'flags' => Stmt\Class_::MODIFIER_PUBLIC]);
            }
        } else if ($node instanceof Stmt\Property) {
            return NodeTraverser::REMOVE_NODE;
        }
    }

    private function collectMeta(array $nodes): void {
        $traverser = new NodeTraverser();
        $this->meta = new MetaCollector();
        $traverser->addVisitor($this->meta);
        $traverser->traverse($nodes);
    }
}

class MetaCollector extends NodeVisitorAbstract {
    private $classes = [];
    private $className;

    public function hasConstructor(string $className): bool {
        return !empty($this->classes[$className]['constructor']);
    }

    public function hasProperties(string $className): bool {
        return isset($this->classes[$className]['properties']) && count($this->classes[$className]['properties']);
    }

    public function enterNode(Node $node) {
        if ($node instanceof Stmt\Class_) {
            $this->className = (string)$node->name;
            $this->classes[$this->className] = [];
        } elseif ($node instanceof Stmt\PropertyProperty) {
            Must::beNotEmpty($this->className);
            $this->classes[$this->className]['properties'][] = $node;
            return;
        } elseif ($node instanceof Stmt\ClassMethod && (string)$node->name === '__construct') {
            Must::beNotEmpty($this->className);
            $this->classes[$this->className]['constructor'] = $node;
        }
    }

    public function leaveNode(Node $node) {
        if ($node instanceof Stmt\Class_) {
            $this->className = null;
        }
    }

    public function beforeTraverse(array $nodes) {
        $this->classes = [];
        $this->className = null;
    }

    public function afterTraverse(array $nodes) {
        return null;
    }

    public function properties(string $className): array {
        return $this->classes[$className]['properties'] ?? [];
    }
}