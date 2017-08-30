<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Validation;

class HostNameValidator extends Validator {
    public function isValid($host) {
        return preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host) && strlen($host < 255);
    }
}
