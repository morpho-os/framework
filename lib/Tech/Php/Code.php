<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php;

use Morpho\Base\NotImplementedException;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function in_array;
use function is_string;
use function Morpho\Tech\Php\Parsing\parse;
use function Morpho\Tech\Php\Parsing\pp;
use function Morpho\Tech\Php\Parsing\visit;
use function preg_replace;
use function token_get_all;
use function var_export;

class Code {
    public static function format(string $php): string {
        throw new NotImplementedException();
    }

    public static function varToStr(mixed $var, bool $removeNumericKeys = true): string {
        $nodes = parse('<?php ' . var_export($var, true) . ';');

        $visitor = new class ($removeNumericKeys) extends NodeVisitorAbstract {
            public function __construct(public $removeNumericKeys) {

            }

            public function enterNode(Node $node) {
                if ($node instanceof Node\Expr\Array_) {
                    $node->setAttribute('kind', Node\Expr\Array_::KIND_SHORT);
                } elseif ($node instanceof Node\Expr\ArrayItem) {
                    if ($this->removeNumericKeys && $node->key instanceof Node\Scalar\LNumber) {
                        $node->key = null;
                    }
                }
                return parent::enterNode($node);
            }
        };
        visit($nodes, [$visitor]);

        return rtrim(pp($nodes), ';');
    }

    public static function removeComments(string $source): string {
        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a newline
        $output = preg_replace(['/\s+$/Sm', '/\n+/S'], "\n", $output);

        return $output;
    }
}
