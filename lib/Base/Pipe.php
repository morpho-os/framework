<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * Pipe: Step[], where Phase is callable
 */
abstract class Pipe implements IPipe {
    protected int $index = 0;

    public function __invoke(mixed $val): mixed {
        foreach ($this as $fn) {
            $val = $fn($val);
        }
        return $val;
    }

    /**
     * Returns current step
     */
    abstract public function current(): callable;

    public function next(): void {
        ++$this->index;
    }

    public function key(): int|string {
        return $this->index;
    }

    public function valid(): bool {
        return $this->index >= 0 && $this->index < $this->count();
    }

    abstract public function count(): int;

    public function rewind(): void {
        $this->index = 0;
    }
}