<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Fs;

class FileNotFoundException extends Exception {
    public function __construct($filePath = null) {
        $message = null;
        if (null !== $filePath) {
            $message = "The file '$filePath' does not exist";
        }
        parent::__construct($message);
    }
}
