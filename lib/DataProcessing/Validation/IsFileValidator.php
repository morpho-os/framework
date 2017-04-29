<?php
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