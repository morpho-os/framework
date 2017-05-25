<?php
declare(strict_types=1);
namespace Morpho\Code;

use PhpParser\Node;

class SimpleTraverser {
    public function traverseArray(array $nodes, \Closure $fn) {
        foreach ($nodes as $node) {
            if ($node instanceof Node) {
                $fn($node);
                $this->traverseNode($node, $fn);
            }
        }
    }

    private function traverseNode(Node $node, \Closure $fn) {
        foreach ($node->getSubNodeNames() as $name) {
            $subNode =& $node->$name;
            if (\is_array($subNode)) {
                $this->traverseArray($subNode, $fn);
            } elseif ($subNode instanceof Node) {
                $this->traverseNode($subNode, $fn);
            }
        }
    }
}