<?php
namespace Morpho\Base;

class PropertyNotSetException extends \RuntimeException {
    public function __construct($object, $property) {
        $class = is_string($object) ? $object : get_class($object);
        parent::__construct("The property '$class::$property' was not set.");
    }
}
