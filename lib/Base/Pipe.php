<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * Pipe/Pipeline is sequence of stages/phases, where each stage is callable with the type:
 *     (mixed $value): mixed
 */
class Pipe extends \ArrayObject implements IFn {
    private $beforeEachAction;
    private $afterEachAction;

    public function __invoke($value) {
        foreach ($this as $stage) {
            $value = $this->runStage($stage, $value);
        }
        return $value;
    }

    public function append($value): self {
        parent::append($value);
        return $this;
    }

    public function setBeforeEachAction(callable $action) {
        $this->beforeEachAction = $action;
    }

    public function setAfterEachAction(callable $action) {
        $this->afterEachAction = $action;
    }

    protected function runStage(callable $stage, $value) {
        if ($this->beforeEachAction) {
            $value = ($this->beforeEachAction)($value);
        }
        $value = $stage($value);
        if ($this->afterEachAction) {
            $value = ($this->afterEachAction)($value);
        }
        return $value;
    }
}
