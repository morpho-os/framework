<?php
namespace Morpho\Base;

use ArrayIterator as BaseArrayIterator;

class ArrayIterator extends BaseArrayIterator {
    public function toArray(): array {
        return $this->getArrayCopy();
    }

    public function item($offset) {
        return $this->offsetGet($offset);
    }

    public function clear(): void {
        for ($this->rewind(); $this->valid(); $this->offsetUnset($this->key()));
    }

    public function isEmpty(): bool {
        return $this->count() === 0;
    }
}
