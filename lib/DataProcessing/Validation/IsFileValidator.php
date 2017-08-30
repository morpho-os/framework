<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Validation;

class IsFileValidator extends Validator {
    const IS_NOT_FILE = 'isNotFile';

    protected $messageTemplates = [
        self::IS_NOT_FILE => "The provided path is not a file.",
    ];

    public function isValid($path) {
        if (!is_file($path)) {
            $this->error(self::IS_NOT_FILE);
            return false;
        }
        return true;
    }
}
