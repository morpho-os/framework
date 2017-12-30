<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use SplStack;

class Stack extends SplStack {
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
    public function peek() {
        return $this->top();
    }*/
}
