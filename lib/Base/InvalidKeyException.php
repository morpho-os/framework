<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use RuntimeException;

/**
 * Must be thrown when key or index of array is invalid.
 */
class InvalidKeyException extends RuntimeException {
    public function __construct($key) {
        parent::__construct("The key '$key' is invalid.");
    }
}
