<?php
namespace Morpho\Filter;

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
