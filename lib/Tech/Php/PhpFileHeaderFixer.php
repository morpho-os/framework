<?php declare(strict_types=1);


namespace Morpho\Tech\Php;


use Morpho\Base\Err;
use Morpho\Base\IFn;
use Morpho\Base\Ok;
use Morpho\Base\Result;
use Morpho\Fs\Path;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Traversable;
use UnexpectedValueException;

use function Morpho\Base\init;
use function Morpho\Base\last;
use function Morpho\Base\q;

class PhpFileHeaderFixer implements IFn {
    /**
     * @param mixed $context
     * @return Result
     *     Ok: if fix was successful.
     *     Err: otherwise
     */
    public function __invoke(mixed $context): Result {
        $result = $this->check($context);
        if (!$result->isOk()) {
            if ($context['shouldFix']($result)) {
                /** @noinspection PhpIncompatibleReturnTypeInspection */
                return $this->fix($result->val())
                    ->map(
                        function ($context) {
                            if (!$context['dryRun']) {
                                file_put_contents($context['filePath'], $context['text']);
                            }
                            return $context;
                        }
                    );
            } else {
                return new Ok($result->val());
            }
        }
        return $result;
    }

    /**
     * @param array $context
     * @return Result Err if the file has to be fixed later, and Ok otherwise.
     */
    public function check(array $context): Result {
        $nsCheckResult = $this->checkNamespaces($context);
        $classTypeCheckResult = $this->checkClassTypes($context);

        $visitor = new class ($this->licenseComment()) extends NodeVisitorAbstract {
            public bool $hasValidDeclare = false;
            public bool $hasDeclare = false;
            public bool $hasLicenseComment = false;
            public bool $hasStmts = false;
            private bool $checkLicenseComment = true;

            public function __construct(private string $licenseComment) {
            }

            public function enterNode(Node $node) {
                if ($node instanceof Node\Stmt) {
                    if (!$this->hasStmts && $node instanceof Node\Stmt\InlineHTML && substr($node->value, 0, 2) == '#!') {
                        return null; // skip shebang
                    }
                    $this->hasStmts = true;
                    if ($node instanceof Node\Stmt\Declare_) {
                        $this->hasDeclare = true;
                        if (isset($node->declares[0]) && $node->declares[0] instanceof Node\Stmt\DeclareDeclare && $node->declares[0]->key->name === 'strict_types' && $node->declares[0]->value->value === 1) {
                            $this->hasValidDeclare = true;
                        }
                        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    } elseif ($this->checkLicenseComment && !$node instanceof Node\Stmt\Declare_) { // check that the first encountered statement except declare has the license comment
                        $licenseComment = trim($this->licenseComment);
                        foreach ($node->getComments() as $comment) {
                            if ($comment instanceof Comment\Doc && trim($comment->getText()) === $licenseComment) {
                                $this->hasLicenseComment = true;
                                $this->checkLicenseComment = false;
                                break;
                            }
                        }
                    }
                }
                return null;
            }
        };

        visit(
            $this->parse($context),
            [$visitor]
        );

        $result = array_merge(
            $context,
            [
                'hasStmts'             => $visitor->hasStmts,
                'hasDeclare'           => $visitor->hasDeclare,
                'hasValidDeclare'      => $visitor->hasValidDeclare,
                'hasLicenseComment'    => $visitor->hasLicenseComment,
                'nsCheckResult'        => $nsCheckResult,
                'classTypeCheckResult' => $classTypeCheckResult,
            ]
        );
        return $visitor->hasValidDeclare && $nsCheckResult->isOk() && $classTypeCheckResult->isOk() && $visitor->hasLicenseComment
            ? new Ok($result)
            : new Err($result);
    }

    public function fix(array $context): Result {
        if (!$context['hasStmts']) {
            return new Err("The file " . q($context['filePath']) . ' does not have PHP statements');
        }

        if ($context['hasDeclare']) {
            if (!$context['hasValidDeclare']) {
                return new Err(
                    array_merge(
                        $context,
                        ['reason' => "Unable to fix declare() for the file " . q($context['filePath']) . '. Reason: file has unknown `declare` statement.'],
                    )
                );
            }
        } else {
            $context = $this->addDeclare($context);
        }

        if (!$context['classTypeCheckResult']->isOk()) {
            return new Err(
                array_merge(
                    $context,
                    ['reason' => "Unable to fix the file " . q($context['filePath']) . '. Reason: file contains invalid class(es).'],
                )
            );
        }

        if (!$context['nsCheckResult']->isOk()) {
            $context = $this->fixNs($context);
        }

        $context = $this->fixLicenseComment($context); // Fix always.

        return new Ok($context);
    }

    private function fixNs(array $context): array {
        $fix = $context['nsCheckResult']->val();

        $visitor = new class ($fix) extends NodeVisitorAbstract {
            public bool $fixed = false;

            private array $fix;

            public function __construct(array $fix) {
                $this->fix = $fix;
            }

            public function enterNode(Node $node) {
                if (!$this->fixed) {
                    if ($node instanceof Node\Stmt\Declare_) {
                        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                    if ($node instanceof Node\Stmt\Namespace_) {
                        if ($node->name) {
                            $node->name->parts = explode('\\', $this->fix['expected']); // fix only if non-global namespace.
                        }
                        $this->fixed = true;
                    }
                }
                return null;
            }
        };

        return $this->visit(
            $context,
            [$visitor],
            afterVisit: function ($nodes, $visitors) use ($fix) {
            $visitor = $visitors[0];
            if (!$visitor->fixed) {
                if ($nodes) {
                    if (!$nodes[0] instanceof Node\Stmt\Declare_) {
                        throw new UnexpectedValueException();
                    }
                    array_splice($nodes, 1, 0, [new Node\Stmt\Namespace_(new Node\Name($fix['expected']))]);
                }
            }
            return $nodes;
        }
        );
    }

    private function checkNamespaces(array $context): Result {
        $relPath = Path::rel($context['filePath'], $context['baseDirPath']);
        $expectedNs = rtrim($context['ns'], '\\');
        $nsSuffix = init(str_replace('/', '\\', $relPath), '\\');
        if ($nsSuffix !== '') {
            $expectedNs .= '\\' . $nsSuffix;
        }
        foreach (self::namespaces($context['filePath']) as $ns) {
            // We are checking only the first namespace.
            if ($ns !== $expectedNs) {
                return new Err(['expected' => $expectedNs, 'actual' => $ns]);
            }
            return new Ok(['expected' => $expectedNs, 'actual' => $ns]);
        }
        return new Err(['expected' => $expectedNs, 'actual' => null]);
    }

    private function checkClassTypes(array $context): Result {
        $mustHaveClasses = ctype_upper(ltrim(basename($context['filePath'], '_'))[0]); // Must have classes if filename starts with [A-Z]
        $filePath = $context['filePath'];
        $expectedClassName = Path::dropExt(basename($filePath));
        foreach (self::classes($filePath) as $className) {
            $shortClassName = last($className, '\\');
            if ($shortClassName !== $expectedClassName) {
                return new Err(
                    [
                        'expected' => $expectedClassName,
                        'actual'   => $shortClassName,
                    ]
                );
            }
            // We are checking only the first class.
            return new Ok(
                [
                    'expected' => $expectedClassName,
                    'actual'   => $shortClassName,
                ]
            );
        }
        if ($mustHaveClasses) {
            return new Err(['expected' => $expectedClassName, 'actual' => null]);
        }
        return new Ok(['expected' => null, 'actual' => null]);
    }

    /**
     * @param string $filePath
     * @return Traversable|string[]
     */
    private function namespaces(string $filePath): iterable {
        $rFile = new FileReflection($filePath);
        foreach ($rFile->namespaces() as $rNamespace) {
            yield $rNamespace->name();
        }
    }

    /**
     * @param string $filePath
     * @return Traversable|string[]
     */
    private function classes(string $filePath): iterable {
        return (new ClassTypeDiscoverer())->classTypesDefinedInFile($filePath);
    }

    private function licenseComment(): string {
        return <<<'OUT'
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
OUT;
    }

    private function addDeclare(array $context): array {
        $nodes = $this->parse($context);
        array_unshift(
            $nodes,
            new Node\Stmt\Declare_(
                [
                    new Node\Stmt\DeclareDeclare(
                        new Node\Identifier('strict_types'),
                        new Node\Scalar\LNumber(1),
                    ),
                ]
            )
        );
        $context['text'] = $this->ppFile($nodes);
        return $context;
    }

    private function fixLicenseComment(array $context): array {
        $visitor = new class ($this->licenseComment()) extends NodeVisitorAbstract {
            private bool $licenseCommentRemoved = false;
            private bool $licenseCommentAdded = false;

            public function __construct(private string $licenseComment) {
            }

            public function enterNode(Node $node) {
                if ($node instanceof Node\Stmt || $node instanceof Node\Expr) {
                    if (!$this->licenseCommentRemoved) {
                        $this->licenseCommentRemoved = $this->removeLicenseComment($node);
                    }

                    if ($node instanceof Node\Stmt\Declare_) {
                        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }

                    if (!$this->licenseCommentAdded && $node instanceof Node\Stmt) {
                        $comments = $node->getComments();
                        array_unshift($comments, new Comment\Doc($this->licenseComment));
                        $node->setAttribute('comments', $comments);
                        $this->licenseCommentAdded = true;
                    }
                }
                return null;
            }

            private function removeLicenseComment(Node $node): bool {
                $attributes = $node->getAttributes();
                $found = false;
                if (isset($attributes['comments'])) {
                    $licenseComment = trim($this->licenseComment);
                    foreach ($attributes['comments'] as $key => $comment) {
                        if ($comment instanceof Comment\Doc && trim($comment->getText()) === $licenseComment) {
                            unset($attributes['comments'][$key]);
                            $found = true;
                        }
                    }
                    if ($found) {
                        $attributes['comments'] = array_values($attributes['comments']);
                        $node->setAttributes($attributes);
                    }
                }
                return $found;
            }
        };
        return $this->visit($context, [$visitor]);
    }

    private function parse(array $context): array {
        return isset($context['text']) ? parse($context['text']) : parseFile($context['filePath']);
    }

    /**
     * @param array $context
     * @param array $visitors
     * @param callable|null $beforeVisit
     * @param callable|null $afterVisit
     * @return array Modified $context.
     */
    private function visit(array $context, array $visitors, callable $beforeVisit = null, callable $afterVisit = null): array {
        $nodes = $this->parse($context);
        if ($beforeVisit) {
            $nodes = $beforeVisit($nodes, $visitors);
        }
        visit($nodes, $visitors);
        if ($afterVisit) {
            $nodes = $afterVisit($nodes, $visitors);
        }
        $context['text'] = $this->ppFile($nodes);
        return $context;
    }

    private function ppFile(array $nodes): string {
        $text = ppFile($nodes);
        $text = preg_replace('~^\\<\\?php\\s+declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;~si', '<?php declare(strict_types=1);', $text);
        return $text;
    }
}