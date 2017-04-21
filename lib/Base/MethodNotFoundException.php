<?php
namespace Morpho\Base;

use BadMethodCallException;

class MethodNotFoundException extends BadMethodCallException {
    /**
     * @param string|object $object
     * @param string $method
     */
    public function __construct($object, $method) {
        $class = is_string($object) ? $object : get_class($object);
        parent::__construct("The method '$class::$method()' was not found.");
    }
}
