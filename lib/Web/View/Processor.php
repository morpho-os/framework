<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\IFn;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;

class Processor implements IFn {
    /**
     * @param \ArrayAccess|array $context
     */
    public function __invoke($context) {
        $code = $context['code'];

        $ast = $this->parse($code);

        unset($context['code']);

        $ast = $this->rewrite($ast, $context);

        $context['code'] = $this->pp($ast);

        return $context;
    }

    public function rewrite(array $ast, \ArrayAccess $context): array {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new AstRewriter($this, $context));
        return $traverser->traverse($ast);
    }

    public function pp(array $ast): string {
        return (new PrettyPrinter())->prettyPrintFile($ast);
    }

    public function parse(string $code): array {
        $parser = new Parser(new Lexer());
        return $parser->parse($code);
    }
}
