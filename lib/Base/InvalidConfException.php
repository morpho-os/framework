<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use RuntimeException;

class InvalidConfException extends RuntimeException {
    public function __construct($message = null) {
        if (\is_array($message)) {
            $message = 'Invalid conf keys: ' . shorten(\implode(', ', \array_keys($message)), 80);
        } elseif (null === $message) {
            $message = "Invalid conf has been provided";
        }
        parent::__construct($message);
    }
}
