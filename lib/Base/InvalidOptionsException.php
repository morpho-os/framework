<?php
namespace Morpho\Base;

use RuntimeException;

class InvalidOptionsException extends RuntimeException {
    public function __construct($message = null) {
        if (is_array($message)) {
            // invalid options have been passed as array
            $message = 'Invalid options: ' . shorten(implode(', ', array_keys($message)), 80);
        } elseif (null === $message) {
            $message = "Invalid options have been provided";
        }
        parent::__construct($message);
    }
}
