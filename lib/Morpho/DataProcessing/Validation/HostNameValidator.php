<?php
namespace Morpho\DataProcessing\Validation;

class HostNameValidator extends Validator {
    public function isValid($host) {
        return preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host) && strlen($host < 255);
    }
}