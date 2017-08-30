<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Di;

class ServiceNotFoundException extends \Exception {
    public function __construct($id) {
        parent::__construct("The service with ID '$id' was not found.");
    }
}
