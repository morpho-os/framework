<?php declare(strict_types=1);


namespace Morpho\Tech\Php\Fix;


use Morpho\Base\Err;
use Morpho\Base\IFn;
use Morpho\Base\Ok;
use Morpho\Base\Result;
use Morpho\Fs\Path;
use Morpho\Tech\Php\Reflection\ClassTypeDiscoverer;
use Morpho\Tech\Php\Reflection\FileReflection;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

use function Morpho\Base\init;
use function Morpho\Base\last;
use function Morpho\Base\q;
use function Morpho\Tech\Php\parse;
use function Morpho\Tech\Php\parseFile;
use function Morpho\Tech\Php\ppFile;
use function Morpho\Tech\Php\visit;
use function Morpho\Tech\Php\visitFile;

class PhpFileHeaderFixer implements IFn {
    public function __invoke(mixed $context): mixed {
        $result = $this->check($context);
        if (!$result->isOk()) {
            if ($context['shouldFix']($result)) {
                return $this->fix($result);
            }
        }
        return $result;
    }

    private function fix($checkResult): Result {
        $context = $checkResult->val();

        if ($context['hasDeclare']) {
            if (!$context['hasValidDeclare']) {
                return new Err(
                    array_merge(
                        $checkResult->val(),
                        ['reason' => "Unable to fix declare() for the file " . q($context['filePath']) . '. Reason: file has unknown `declare` statement.'],
                    )
                );
            }
        } else {
            $this->addDeclare($context);
        }

        if (!$context['classTypeCheckResult']->isOk()) {
            return new Err(
                array_merge(
                    $checkResult->val(),
                    ['reason' => "Unable to fix the file " . q($context['filePath']) . '. Reason: file contains invalid class(es).'],
                )
            );
        }

        if (!$context['nsCheckResult']->isOk()) {
            $context = $this->fixNs($context);
        }

        return new Ok($context);
    }

    private function fixNs(array $context): array {
        $fix = $context['nsCheckResult']->val();

        $visitor = new class ($fix, $this->licenseComment()) extends NodeVisitorAbstract {
            public bool $fixed = false;

            public bool $removeLicenseComment;

            private array $fix;
            private string $licenseComment;

            public function __construct(array $fix, string $licenseComment) {
                $this->fix = $fix;
                $this->licenseComment = $licenseComment;
            }

            public function enterNode(Node $node) {
                if ($this->removeLicenseComment) {
                    $this->removeLicenseComment($node);
                }
                if (!$this->fixed) {
                    if ($node instanceof Node\Stmt\Declare_) {
                        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                    if ($node instanceof Node\Stmt\Namespace_) {
                        $this->removeLicenseComment($node);
                        $node->name->parts = explode('\\', $this->fix['expected']);
                        $node->setDocComment(new Comment\Doc($this->licenseComment));
                        $this->fixed = true;
                    }
                }
                return null;
            }

            private function removeLicenseComment($node) {
                $docComment = $node->getDocComment();
                if ($docComment && trim($docComment->getText()) === trim($this->licenseComment)) {
                    $attributes = $node->getAttributes();
                    if (count($attributes['comments']) !== 1 || !$attributes['comments'][0] instanceof Comment\Doc) {
                        throw new \UnexpectedValueException();
                    }
                    unset($attributes['comments']);
                    $node->setAttributes($attributes);
                    //$this->removeLicenseComment = false;
                }
            }
        };
        if ($context['hasLicenseComment']) {
            $visitor->removeLicenseComment = true;
        }

        $nodes = visitFile($context['filePath'], [$visitor]);

        if (!$visitor->fixed) {
            if ($nodes) {
                if (!$nodes[0] instanceof Node\Stmt\Declare_) {
                    throw new \UnexpectedValueException();
                }
                $nsNode = new Node\Stmt\Namespace_(new Node\Name($fix['expected']));
                $nsNode->setDocComment(new Comment\Doc($this->licenseComment()));
                array_splice($nodes, 1, 0, [$nsNode]);
            }
        }

        $context['fixed'] = ppFile($nodes);
        if (!$context['dryRun']) {
            file_put_contents($context['filePath'], d($context['fixed']));
        }
        return $context;
    }

    private function check($context): Result {
        $nsCheckResult = $this->checkNamespaces($context);
        $classTypeCheckResult = $this->checkClassTypes($context);

        $code = file_get_contents($context['filePath']);
        $nodes = parse($code);

        $visitor = new class ($this->licenseComment()) extends NodeVisitorAbstract {
            public bool $hasValidDeclare = false;
            public bool $hasDeclare = false;
            public bool $hasLicenseComment = false;

            private Node $prevStmt;

            private bool $checkLicenseComment = true;

            public function __construct(private string $licenseComment) {
            }

            public function enterNode(Node $node) {
                if ($node instanceof Node\Stmt) {
                    if ($node instanceof Node\Stmt\Declare_) {
                        $this->hasDeclare = true;
                        if (isset($node->declares[0]) && $node->declares[0] instanceof Node\Stmt\DeclareDeclare && $node->declares[0]->key->name === 'strict_types' && $node->declares[0]->value->value === 1) {
                            $this->hasValidDeclare = true;
                        }
                        $this->prevStmt = $node;
                        return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    } elseif ($this->checkLicenseComment && $node instanceof Node\Stmt && $this->prevStmt instanceof Node\Stmt\Declare_) {
                        $docComment = $node->getDocComment();
                        $this->hasLicenseComment = $docComment && trim($docComment->getText()) === trim($this->licenseComment);
                        $this->checkLicenseComment = false;
                    }
                }
                return null;
            }
        };

        visit($nodes, [$visitor]);

        $result = array_merge(
            $context,
            [
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

    public function checkNamespaces($context): Result {
        $relPath = Path::rel($context['filePath'], $context['baseDirPath']);
        $expectedNs = rtrim($context['ns'], '\\');
        $nsSuffix = init(str_replace('/', '\\', $relPath), '\\');
        if ($nsSuffix !== '') {
            $expectedNs .= '\\' . $nsSuffix;
        }
//        $allowGlobalNs = ctype_lower(ltrim(basename($relPath), '_')[0]); // Allow only if filename starts with [a-z]
        foreach (self::namespaces($context['filePath']) as $ns) {
/*            if (null === $ns && $allowGlobalNs) {
                // null means global
                continue;
            }*/
            // We are checking only the first namespace.
            if ($ns !== $expectedNs) {
                return new Err(['expected' => $expectedNs, 'actual' => $ns]);
            }
            return new Ok(['expected' => $expectedNs, 'actual' => $ns]);
        }
/*        if ($allowGlobalNs) {
            return new Ok(['expected' => $expectedNs, 'actual' => null]);
        }*/
        return new Err(['expected' => $expectedNs, 'actual' => null]);
    }

    private function checkClassTypes($context): Result {
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
        return new Ok(['expected' => $expectedClassName, 'actual' => null]);
    }

    /**
     * @param string $filePath
     * @return \Traversable|string[]
     */
    private function namespaces(string $filePath): iterable {
        $rFile = new FileReflection($filePath);
        foreach ($rFile->namespaces() as $rNamespace) {
            yield $rNamespace->name();
        }
    }

    /**
     * @param string $filePath
     * @return \Traversable|string[]
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

    private function addDeclare($context) {
        d($context);
    }
}