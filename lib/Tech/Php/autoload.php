<?php declare(strict_types=1);
namespace Morpho\Tech\Php;

require __DIR__ . '/Debug/autoload.php';
require __DIR__ . '/Autoloading/autoload.php';

use PhpParser\Node\Stmt\Class_ as ClassStmt;
use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
use PhpParser\Node\Stmt\Trait_ as TraitStmt;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

function parseFile(string $filePath): ?array {
    return parse(\file_get_contents($filePath));
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
    $pp = new PrettyPrinter(['shortArraySyntax' => true]);
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
