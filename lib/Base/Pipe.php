<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

/**
 * Pipe/Pipeline is sequence of stages/phases, where each phase is callable with the type:
 *     (mixed $value): mixed
 */
class Pipe extends \ArrayObject implements IFn {
    public function __invoke($value) {
        foreach ($this as $stage) {
            $value = $stage($value);
        }
        return $value;
    }

    public function append($value): self {
        parent::append($value);
        return $this;
    }
}