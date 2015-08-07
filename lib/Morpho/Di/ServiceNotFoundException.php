<?php
namespace Morpho\Di;

class ServiceNotFoundException extends \Exception {
    public function __construct($id) {
        parent::__construct("The service with ID '$id' was not found.");
    }
}
