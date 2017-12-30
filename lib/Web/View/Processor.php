<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\NotImplementedException;
use PhpParser\{
    Node\Expr, NodeVisitorAbstract, Node, Node\Arg as ArgNode, Node\Name as NameNode, Node\Scalar\MagicConst\Dir as DirMagicConst, Node\Scalar\MagicConst\File as FileMagicConst, Node\Scalar\String_ as StringScalar, Node\Stmt\Echo_ as EchoStatement, Node\Expr\FuncCall as FuncCallExpr, Node\Expr\ConstFetch as ConstFetchExpr, Node\Expr\Include_ as IncludeExpr, Comment\Doc as DocComment, PrettyPrinter\Standard as PrettyPrinter, Node\Stmt\Expression
};

class Processor extends NodeVisitorAbstract {
    protected $filePath;

    protected $compiler;

    protected $appendSourceInfo = true;

    public function __construct($filePath, $compiler, $appendSourceInfo = true) {
        $this->filePath = $filePath;
        $this->compiler = $compiler;
        $this->appendSourceInfo = $appendSourceInfo;
    }

    public function enterNode(Node $node) {
        if ($node instanceof DirMagicConst) {
            return new StringScalar(dirname($this->filePath));
        } elseif ($node instanceof FileMagicConst) {
            return new StringScalar($this->filePath);
        }
    }

    public function leaveNode(Node $node) {
        if ($node instanceof EchoStatement) {
            return new EchoStatement(
                [
                    new FuncCallExpr(
                        new NameNode(
                            ['htmlspecialchars']
                        ),
                        [
                            new ArgNode(
                                $node->exprs[0]
                            ),
                            new ArgNode(
                                new ConstFetchExpr(
                                    new NameNode(['ENT_QUOTES'])
                                )
                            ),
                        ]
                    ),
                ]
            );
        } elseif ($node instanceof Expression) {
            $expr = $node->expr;
            if ($expr instanceof IncludeExpr) {
                if ($expr->type !== IncludeExpr::TYPE_REQUIRE) {
                    throw new NotImplementedException(
                        "Only 'require' expression is supported, the support of include|include_once|require_once was not implemented yet"
                    );
                }
                return $this->evalRequire($expr->expr);
                /*
                $oldComments = (array)$node->getAttribute('comments');
                $nodes = $this->evalRequire($node->expr);
                if (count($oldComments)) {
                    $expr->setAttribute('comments', $oldComments);
                }
                return $node;

                $nodes = $this->evalRequire($expr->expr);
                return $nodes;
                */
            }
        }
    }

    public function beforeTraverse(array $nodes) {
        $this->prependCommentLine($nodes, "Source file: '{$this->filePath}'");
    }

    protected function evalRequire(Expr $expr) {
        $filePath = $this->evalExpr($expr);
        $code = file_get_contents($filePath);
        return $this->compiler->__invoke($code, false);
    }

    protected function evalExpr(Expr $expr) {
        $printer = new PrettyPrinter();
        return eval('return ' . $printer->prettyPrintExpr($expr) . ';');
    }

    private function prependCommentLine(array $nodes, $commentLine) {
        if ($this->appendSourceInfo && count($nodes)) {
            $node = $nodes[0];
            $node->setAttribute(
                'comments',
                array_merge(
                    [new DocComment("/**\n * $commentLine\n */")],
                    (array)$node->getAttribute('comments')
                )
            );
        }
    }
}
