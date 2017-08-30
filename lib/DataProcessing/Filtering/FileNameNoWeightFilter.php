<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Filtering;

use Morpho\Fs\Path;

class FileNameNoWeightFilter extends Filter {
    public function filter($path) {
        $path = Path::normalize($path);
        $parts = explode('/', $path);
        $fileName = preg_replace('~^[0-9][\d.]*[-_]~si', '', array_pop($parts));
        if (count($parts)) {
            return implode('/', $parts) . '/' . $fileName;
        }
        return $fileName;
    }
}
