<?php declare(strict_types=1);
namespace Morpho\Tech\Php\Parsing;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;

function parseFile(string $filePath): ?array {
    try {
        return parse(\file_get_contents($filePath));
    } catch (\PhpParser\Error $e) {
        throw new \RuntimeException("Unable to parse the file '$filePath'", 0, $e);
    }
}

function parse(string $str): ?array {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    return $parser->parse($str);
}

function visitFile(string $filePath, array $visitors): array {
    $nodes = parseFile($filePath);
    if (null === $nodes) {
        // non-throwing error handler is used and parser was unable to recover from an error.
        throw new \UnexpectedValueException();
    }
    return visit($nodes, $visitors);
}

function visit(array $nodes, array $visitors) {
    $traverser = new NodeTraverser();
    foreach ($visitors as $visitor) {
        $traverser->addVisitor($visitor);
    }
    $traverser->traverse($nodes);
    return $nodes;
}

function pp(array $nodes): string {
    $pp = new PrettyPrinter();
    return $pp->prettyPrint($nodes);
}

function ppFile(array $nodes): string {
    $pp = new PrettyPrinter();
    return $pp->prettyPrintFile($nodes);
}

function isClassType(\PhpParser\Node $node): bool {
    return $node instanceof ClassStmt
        || $node instanceof InterfaceStmt
        || $node instanceof TraitStmt;
}
