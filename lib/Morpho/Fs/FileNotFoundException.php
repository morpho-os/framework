<?php
namespace Morpho\Fs;

class FileNotFoundException extends IoException {
    public function __construct($filePath = null) {
        $message = null;
        if (null !== $filePath) {
            $message = "The file '$filePath' was not found.";
        }
        parent::__construct($message);
    }
}
