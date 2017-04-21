<?php
namespace Morpho\Base;

use RuntimeException;

class OptionRequiredException extends RuntimeException {
    public function __construct($name) {
        parent::__construct("The option '$name' is required.");
    }
}
