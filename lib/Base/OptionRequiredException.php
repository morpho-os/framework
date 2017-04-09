<?php
namespace Morpho\Base;

class OptionRequiredException extends \RuntimeException {
    public function __construct($name) {
        parent::__construct("The option '$name' is required.");
    }
}
