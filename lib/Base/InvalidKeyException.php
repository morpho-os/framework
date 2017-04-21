<?php
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
