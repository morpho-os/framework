<?php
namespace Morpho\Base;

use RuntimeException;

class PropertyNotFoundException extends RuntimeException {
    public function __construct($object, $property) {
        $class = is_string($object) ? $object : get_class($object);
        parent::__construct("The property '$class::$property' was not found.");
    }
}
