<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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
