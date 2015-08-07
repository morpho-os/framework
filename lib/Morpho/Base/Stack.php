<?php
namespace Morpho\Base;

class Stack extends \SplStack {
    /**
     * Removes all items from stack.
     */
    public function clear() {
        while (!$this->isEmpty()) {
            $this->pop();
        }
    }

    /**
     * Replaces top value with provided value.
     */
    public function replace($value) {
        $this->pop();
        $this->push($value);
    }

    /**
     * Works like pop() but doesn't remove an item.
     */
    public function peek() {
        return $this->top();
    }
}
