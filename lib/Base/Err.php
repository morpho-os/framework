<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class Err extends Result {
    public function __construct(mixed $val = null) {
        parent::__construct($val);
    }

    public function isOk(): bool {
        return false;
    }

    public function jsonSerialize() {
        return ['err' => $this->val];
    }
}
