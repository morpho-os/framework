<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class ArrPipe implements IPipe {
    protected array $phases;

    public function __construct(array $phases = null) {
        $this->phases = (array) $phases;
    }

    /**
     * @param mixed $val
     * @return mixed
     */
    public function __invoke($val) {
        foreach ($this as $fn) {
            $val = $fn($val);
        }
        return $val;
    }

    public function setPhases(array $phases): void {
        $this->phases = $phases;
    }

    public function prependPhase($phase): self {
        array_unshift($this->phases, $phase);
        return $this;
    }

    public function appendPhase(callable $phase): self {
        $this->phases[] = $phase;
        return $this;
    }

    public function deletePhase($index): void {
        unset($this->phases[$index]);
        $this->phases = array_values($this->phases);
    }

    public function phases(): array {
        return $this->phases;
    }

    public function phase(string|int $key): mixed {
        return $this->phases[$key];
    }

    public function current(): callable {
        return current($this->phases);
    }

    public function next(): void {
        next($this->phases);
    }

    public function key() {
        return key($this->phases);
    }

    public function valid(): bool {
        return isset($this->phases[$this->key()]);
    }

    public function rewind(): void {
        reset($this->phases);
    }

    public function count(): int {
        return count($this->phases);
    }
}