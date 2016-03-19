<?php
namespace System\View\Plugin;

use function Morpho\Base\escapeHtml;
use Morpho\Web\View\Plugin;

class DirHierarchyRendererPlugin extends Plugin {
    protected $internalNodeRenderer, $leafNodeRenderer;

    private $parents = [];

    public function __invoke(...$args) {
        return $this->render($args[0]);
    }

    public function render(array $fsEntries): string {
        if (!count($fsEntries)) {
            return '';
        }
        $output = '';
        foreach ($fsEntries as $fsEntry) {
            if (is_array($fsEntry)) {
                $output .= $this->renderInternalNodeEntry($fsEntry);
            } else {
                $output .= $this->renderLeafNodeEntry($fsEntry);
            }
        }
        return '<ul' . (empty($this->parents) ? ' class="fs-tree"' : '') . '>'
            . $output
            . '</ul>';
    }

    public function setInternalNodeRenderer(callable $renderer) {
        $this->internalNodeRenderer = $renderer;
        return $this;
    }

    public function getInternalNodeRenderer(): callable {
        if (null === $this->internalNodeRenderer) {
            $this->internalNodeRenderer = function (string $name, string $renderedChildren): string {
                return '<li class="fs-tree__dir">'
                    . $this->renderCheckbox($name, true)
                    . $renderedChildren
                    . '</li>';
            };
        }
        return $this->internalNodeRenderer;
    }

    public function setLeafNodeRenderer(callable $renderer) {
        $this->leafNodeRenderer = $renderer;
        return $this;
    }

    public function getLeafNodeRenderer(): callable {
        if (null === $this->leafNodeRenderer) {
            $this->leafNodeRenderer = function (string $name): string {
                return '<li class="fs-tree__file">'
                    . $this->renderCheckbox($name, false)
                    . '</li>';
            };
        }
        return $this->leafNodeRenderer;
    }

    protected function renderLeafNodeEntry($fsEntry): string {
        $render = $this->getLeafNodeRenderer();
        return $render($fsEntry);
    }

    protected function renderInternalNodeEntry($fsEntry): string {
        $render = $this->getInternalNodeRenderer();
        $renderedChildren = '';
        if (!empty($fsEntry[1])) {
            array_push($this->parents, $fsEntry[0]);
            $renderedChildren = $this->render($fsEntry[1]);
            array_pop($this->parents);
        }
        return $render($fsEntry[0], $renderedChildren);
    }

    protected function renderCheckbox(string $name, bool $isInternalNode): string {
        $renderInputName = function (string $name): string {
            $parents = $this->parents;
            $parents[] = $name;
            return implode('___', array_map([$this, 'escapeHtml'], $parents));
        };
        return '<input type="checkbox" name="' . ($isInternalNode ? 'dir' : 'file') . '[' . $renderInputName($name) . ']"> ' . $this->escapeHtml($name);
    }

    protected function escapeHtml(string $val): string {
        return escapeHtml($val);
    }
}