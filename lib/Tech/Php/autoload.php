<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace {

    use Morpho\Tech\Php\Debugger;

    require_once __DIR__ . '/Trace.php';
    require_once __DIR__ . '/Frame.php';
    require_once __DIR__ . '/Debugger.php';
    if (!function_exists('d')) {
        function d(...$args) {
            $debugger = Debugger::instance();
            return count($args) ? $debugger->ignoreCaller(__FILE__, __LINE__)->dump(...$args) : $debugger;
        }
    }
    if (!function_exists('dd')) {
        function dd(): void {
            Debugger::instance()->ignoreCaller(__FILE__)->dump();
        }
    }
    if (!function_exists('dt')) {
        function dt(): void {
            Debugger::instance()->ignoreCaller(__FILE__)->trace();
        }
    }
}
namespace Morpho\Tech\Php {

    const LICENSE_COMMENT = "/**\n * This file is part of morpho-os/framework\n * It is distributed under the 'Apache License Version 2.0' license.\n * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.\n */";
    use Composer\Autoload\ClassLoader;
    use PhpParser\Node;
    use PhpParser\Node\Stmt\Class_ as ClassStmt;
    use PhpParser\Node\Stmt\Interface_ as InterfaceStmt;
    use PhpParser\Node\Stmt\Trait_ as TraitStmt;
    use PhpParser\NodeTraverser;
    use PhpParser\ParserFactory;
    use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
    use RuntimeException;
    use UnexpectedValueException;

    use function file_get_contents;
    use function is_array;
    use function spl_autoload_functions;

    function parseFile(string $filePath): ?array {
        return parse(file_get_contents($filePath));
    }

    /**
     * @param string $text Text to parse.
     * @return Node\Stmt[]|null Array of statements, representing the text.
     */
    function parse(string $text): ?array {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        return $parser->parse($text);
    }

    function visitFile(string $filePath, array $visitors): array {
        $nodes = parseFile($filePath);
        if (null === $nodes) {
            // non-throwing error handler is used and parser was unable to recover from an error.
            throw new UnexpectedValueException();
        }
        return visit($nodes, $visitors);
    }

    function visit(array $nodes, array $visitors): array {
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

    function isClassType(Node $node): bool {
        return $node instanceof ClassStmt || $node instanceof InterfaceStmt || $node instanceof TraitStmt;
    }

    /**
     * Returns the first found Composer's autoloader - an instance of the \Composer\Autoloader\ClassLoader.
     */
    function composerAutoloader(): ClassLoader {
        foreach (spl_autoload_functions() as $callback) {
            if (is_array($callback) && $callback[0] instanceof ClassLoader && $callback[1] === 'loadClass') {
                return $callback[0];
            }
        }
        throw new RuntimeException("Unable to find the Composer's autoloader in the list of autoloaders");
    }

    function isShebangNode(Node $node): bool {
        return $node instanceof Node\Stmt\InlineHTML && substr($node->value, 0, 2) == '#!';
    }
}