<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeVisitorAbstract;

class ClassTypeDepsCollector extends NodeVisitorAbstract {
    protected $classTypes = [];

    public function classTypes(): array {
        $classTypes = $this->classTypes;
        if (\in_array('self', $classTypes) || \in_array('static', $classTypes) || \in_array('parent', $classTypes)) {
            throw new \RuntimeException("self|static|parent should not be present as class type");
        }
        return $classTypes;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Function_ || $node instanceof Closure) {
            if ($node->returnType && $node->returnType instanceof FullyQualified) {
                $this->classTypes[] = \implode('\\', $node->returnType->parts);
            }
        } elseif ($node instanceof ClassMethod) {
            if ($node->returnType && $node->returnType instanceof FullyQualified) {
                $this->classTypes[] = \implode('\\', $node->returnType->parts);
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
        } elseif ($node instanceof Node\Stmt\Trait_) {
            // @TODO: Skip here??
            //$this->curNode = $node->namespacedName->toString();
        } elseif ($node instanceof TryCatch) {
            foreach ($node->catches as $catchStmt) {
                foreach ($catchStmt->types as $classType) {
                    $this->classTypes[] = \implode('\\', $classType->parts);
                }
            }
        } elseif ($node instanceof TraitUse) {
            foreach ($node->traits as $nodeName) {
                $this->classTypes[] = $nodeName->toString();
            }
        } elseif ($node instanceof New_ && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        } elseif ($node instanceof Param && $node->type instanceof FullyQualified) {
            $this->classTypes[] = \implode('\\', $node->type->parts);
        } elseif ($node instanceof Instanceof_ && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        } elseif (($node instanceof StaticPropertyFetch || $node instanceof ClassConstFetch) && isset($node->class) && $node->class instanceof FullyQualified) {
            $classType = $node->class->toString();
            if ($classType !== 'self' && $classType !== 'static') {
                $this->classTypes[] = $classType;
            }
        } elseif ($node instanceof StaticCall && $node->class instanceof FullyQualified) {
            $this->classTypes[] = $node->class->toString();
        }
    }

    public function beforeTraverse(array $nodes) {
        parent::beforeTraverse($nodes);
        $this->classTypes = [];
    }

    public function afterTraverse(array $nodes) {
        parent::afterTraverse($nodes);
        \sort($this->classTypes);
        $this->classTypes = \array_unique($this->classTypes);
    }
}
