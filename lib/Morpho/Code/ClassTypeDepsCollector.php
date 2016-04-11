<?php
namespace Morpho\Code;

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
        return $this->classTypes;
    }

    public function enterNode(Node $node) {
        if ($node instanceof Function_ || $node instanceof Closure) {
            //$this->curNode = $node->namespacedName->toString();
            if ($node->returnType && $node->returnType instanceof FullyQualified) {
                $this->classTypes[] = implode('\\', $node->returnType->parts);
            }
        } elseif ($node instanceof ClassMethod) {
            if ($node->returnType && $node->returnType instanceof FullyQualified) {
                $this->classTypes[] = implode('\\', $node->returnType->parts);
            }
        } elseif ($node instanceof Class_) {
            /*
            if (!isset($node->namespacedName)) {
                // Anonymous class.
                //$this->curNode = $node->namespacedName->toString();
            }
            */
            if (isset($node->extends)) {
                $this->classTypes[] = $node->extends->toString();
            }
            if (isset($node->implements)) {
                foreach ($node->implements as $nodeName) {
                    $this->classTypes[] = $nodeName->toString();
                }
            }
        } elseif ($node instanceof Interface_) {
            //$this->curNode = $node->namespacedName->toString();
            foreach ($node->extends as $nodeName) {
                $this->classTypes[] = $nodeName->toString();
            }
        } elseif ($node instanceof Node\Stmt\Trait_) {
            //$this->curNode = $node->namespacedName->toString();
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
        sort($this->classTypes);
        $this->classTypes = array_unique($this->classTypes);
    }
}
