<?php
namespace Morpho\Validator;

/**
 * Validator that returns always true.
 */
class TrueValidator extends Validator {
    public function isValid($value) {
        return true;
    }
}
