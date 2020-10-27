<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

class Ok extends Result {
    public const VAL = 'ok';
    /**
     * @param mixed $val
     */
    public function __construct($val = self::VAL) {
        parent::__construct($val);
    }

    public function isOk(): bool {
        return true;
    }
}
