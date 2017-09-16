<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);

namespace Morpho\Code\Js;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\startsWith;
use PhpParser\PrettyPrinter\Standard As PrettyPrinter;
use PhpParser\Node\Expr;

/**
 * Based on code from the nikic/php-parser (\PhpParser\PrettyPrinter)
 * Code generation result is similar in many placed to what the tsc (https://github.com/Microsoft/TypeScript) produces when called with the '--module amd'.
 */
class CodeGenerator extends PrettyPrinter {
    public function prettyPrintFile(array $stmts) : string {
        if (!$stmts) {
            return '"use strict";';
        }
        return "\"use strict\";\n" . $this->prettyPrint($stmts);
    }

    /**
     * Preprocesses the top-level nodes to initialize pretty printer state.
     *
     * @param Node[] $nodes Array of nodes
     */
    protected function preprocessNodes(array $nodes) {
    }

    // Function calls and similar constructs

    protected function pExpr_FuncCall(Expr\FuncCall $node): string {
        $fn = (string) $node->name;
        if (!startsWith($fn, __NAMESPACE__)) {
            throw new NotImplementedException();
        }
        $fn = substr($fn, strlen(__NAMESPACE__) + 1);
        return $fn . '(' . $this->pMaybeMultiline($node->args) . ')';
        // return $this->pCallLhs($node->name)
    }

    private function pMaybeMultiline(array $nodes, $trailingComma = false) {
        if (!$this->hasNodeWithComments($nodes)) {
            return $this->pCommaSeparated($nodes);
        } else {
            return $this->pCommaSeparatedMultiline($nodes, $trailingComma) . $this->nl;
        }
    }

    private function hasNodeWithComments(array $nodes) {
        foreach ($nodes as $node) {
            if ($node && $node->getAttribute('comments')) {
                return true;
            }
        }
        return false;
    }
}