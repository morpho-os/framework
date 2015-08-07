<?php
namespace Morpho\Web\View;

use Morpho\Base\NotImplementedException;
use PhpParser\{
    Lexer,
    Node\Expr,
    NodeTraverser,
    NodeVisitorAbstract,
    Node,
    Node\Arg as ArgNode,
    Node\Name as NameNode,
    Node\Scalar\MagicConst\Dir as DirMagicConst,
    Node\Scalar\MagicConst\File as FileMagicConst,
    Node\Scalar\String_ as StringScalar,
    Node\Stmt\Echo_ as EchoStatement,
    Node\Expr\FuncCall as FuncCallExpr,
    Node\Expr\ConstFetch as ConstFetchExpr,
    Node\Expr\Include_ as IncludeExpr,
    Comment\Doc as DocComment,
    Parser\Php7 as Parser,
    PrettyPrinter\Standard as PrettyPrinter
};

class Processor extends NodeVisitorAbstract {
    protected $filePath;

    protected $parser;

    protected $appendSourceInfo = true;

    public function __construct($filePath, $parser, $appendSourceInfo = true) {
        $this->filePath = $filePath;
        $this->parser = $parser;
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
                array(
                    new FuncCallExpr(
                        new NameNode(
                            array('htmlspecialchars')
                        ),
                        array(
                            new ArgNode(
                                $node->exprs[0]
                            ),
                            new ArgNode(
                                new ConstFetchExpr(
                                    new NameNode(array('ENT_QUOTES'))
                                )
                            ),
                        )
                    ),
                )
            );
        } elseif ($node instanceof IncludeExpr) {
            if ($node->type !== IncludeExpr::TYPE_REQUIRE) {
                throw new NotImplementedException(
                    "Only 'require' expression is supported, the support of include|include_once|require_once was not implemented yet"
                );
            }

            $oldComments = (array)$node->getAttribute('comments');
            $nodes = $this->evaluateRequireExpr($node->expr);
            if (count($nodes) && count($oldComments)) {
                $comments = array_merge(
                    $oldComments,
                    (array)$nodes[0]->getAttribute('comments')
                );
                $nodes[0]->setAttribute('comments', $comments);
            }

            return $nodes;
        }
    }

    public function beforeTraverse(array $nodes) {
        $this->prependCommentLine($nodes, "Source file: '{$this->filePath}'", false);
    }

    protected function prependCommentLine(array $nodes, $commentLine, $asIs) {
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

    /**
     * @param Expr $expr
     * @return \PhpParser\Node[]
     */
    protected function evaluateRequireExpr(Expr $expr) {
        $filePath = $this->evaluateExpr($expr);
        $code = file_get_contents($filePath);

        $parser = new Parser(new Lexer());
        $nodes = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(
            new self($filePath, $parser, $this->appendSourceInfo)
        );

        $nodes = $traverser->traverse($nodes);

        return $nodes;
    }

    protected function evaluateExpr(Expr $expr) {
        $printer = new PrettyPrinter();
        return eval('return ' . $printer->prettyPrintExpr($expr) . ';');
    }
}
