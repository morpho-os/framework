<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Validation;

/**
 * Validator that returns always true.
 */
class TrueValidator extends Validator {
    public function isValid($value) {
        return true;
    }
}
