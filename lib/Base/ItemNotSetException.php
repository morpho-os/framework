<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use RuntimeException;

class ItemNotSetException extends RuntimeException {
    public function __construct($name) {
        parent::__construct("The item '$name' was not set.");
    }
}
