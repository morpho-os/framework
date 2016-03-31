<?php
namespace Morpho\Web\View;

use function Morpho\Base\escapeHtml;

class TreeRendererPlugin extends Plugin {
    protected $internalNodeRenderer, $leafNodeRenderer;

    private $parents = [];

    public function __invoke(...$args) {
        return $this->render($args[0]);
    }

    public function render(array $nodes): string {
        if (!count($nodes)) {
            return '';
        }
        $output = '';
        foreach ($nodes as $label => $node) {
            if (isset($node['nodes'])) {
                $output .= $this->renderInternalNode($node);
            } else {
                $output .= $this->renderLeafNode($node);
            }
        }
        return '<ul' . (empty($this->parents) ? ' class="tree"' : '') . '>'
            . $output
            . '</ul>';
    }

    public function setInternalNodeRenderer(callable $renderer): self {
        $this->internalNodeRenderer = $renderer;
        return $this;
    }

    public function getInternalNodeRenderer(): callable {
        if (null === $this->internalNodeRenderer) {
            $this->internalNodeRenderer = function (array $node, string $renderedChildren): string {
                return '<li class="tree__node tree__node-internal">' . $this->escapeHtml($node['label'])
                    //. $this->renderCheckbox($name, true)
                    . $renderedChildren
                    . '</li>';
            };
        }
        return $this->internalNodeRenderer;
    }

    public function setLeafNodeRenderer(callable $renderer): self {
        $this->leafNodeRenderer = $renderer;
        return $this;
    }

    public function getLeafNodeRenderer(): callable {
        if (null === $this->leafNodeRenderer) {
            $this->leafNodeRenderer = function (array $node): string {
                return '<li class="tree__node tree__node-leaf">'
                    . $this->escapeHtml($node['label'])//$this->renderCheckbox($node['label'], false)
                    . '</li>';
            };
        }
        return $this->leafNodeRenderer;
    }

    protected function renderLeafNode($node): string {
        $render = $this->getLeafNodeRenderer();
        return $render($node);
    }

    protected function renderInternalNode($node): string {
        $render = $this->getInternalNodeRenderer();
        $renderedChildren = '';
        if (!empty($node['nodes'])) {
            array_push($this->parents, $node);
            $renderedChildren = $this->render($node['nodes']);
            array_pop($this->parents);
        }
        return $render($node, $renderedChildren);
    }
/*
    protected function renderCheckbox(string $name, bool $isInternalNode): string {
        $renderInputName = function (string $name): string {
            $parents = $this->parents;
            $parents[] = $name;
            return implode('___', array_map([$this, 'escapeHtml'], $parents));
        };
        return '<input type="checkbox" name="' . ($isInternalNode ? 'internalNode' : 'leafNode') . '[' . $renderInputName($name) . ']"> ' . $this->escapeHtml($name);
    }
*/
    protected function escapeHtml(string $val): string {
        return escapeHtml($val);
    }
}