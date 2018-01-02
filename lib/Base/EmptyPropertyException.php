<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use RuntimeException;

class EmptyPropertyException extends RuntimeException {
    public function __construct($object, $property, $message = null) {
        $class = \is_string($object) ? $object : \get_class($object);
        if (null === $message) {
            $message = "The property '$class::$property' is empty";
        }
        parent::__construct($message);
    }
}
