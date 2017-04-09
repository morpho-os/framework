<?php
namespace Morpho\Base;

class EmptyPropertyException extends \RuntimeException {
    public function __construct($object, $property, $message = null) {
        $class = is_string($object) ? $object : get_class($object);
        if (null === $message) {
            $message = "The property '$class::$property' is empty.";
        }
        parent::__construct($message);
    }
}
