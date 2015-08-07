<?php
namespace Morpho\Base;

class NullPropertyException extends EmptyPropertyException {
    public function __construct($object, $property) {
        parent::__construct($object, $property, "The property '$object::$property' is null.");
    }
}
