<?php
namespace Morpho\Base;

class ArrayIterator extends \ArrayIterator {
    public function toArray() {
        return $this->getArrayCopy();
    }

    public function item($offset) {
        return $this->offsetGet($offset);
    }

    public function clear() {
        for ($this->rewind(); $this->valid(); $this->offsetUnset($this->key())) ;
    }

    public function isEmpty() {
        return $this->count() === 0;
    }
}
