<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use ArrayObject;

/**
 * Pipe/Pipeline: (Phase/Stage)[]
 * Stage: mixed => mixed
 */
class Pipe extends ArrayObject implements IFn {
    public function __invoke($val) {
        foreach ($this as $fn) {
            $val = $fn($val);
        }
        return $val;
    }

    public function append($fn): self {
        parent::append($fn);
        return $this;
    }
}
