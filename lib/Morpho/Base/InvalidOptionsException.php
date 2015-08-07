<?php
namespace Morpho\Base;

class InvalidOptionsException extends \RuntimeException {
    public function __construct($message = null) {
        if (null === $message) {
            $message = "Invalid options were provided.";
        }
        parent::__construct($message);
    }
}
