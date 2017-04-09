<?php
namespace Morpho\DataProcessing\Validation;

/**
 * Validator that returns always true.
 */
class TrueValidator extends Validator {
    public function isValid($value) {
        return true;
    }
}
