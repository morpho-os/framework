<?php
namespace Morpho\Base;

use RuntimeException;

class ItemNotSetException extends RuntimeException {
    public function __construct($name) {
        parent::__construct("The item '$name' was not set.");
    }
}
