<?php
namespace Morpho\Core;

class Version {
    const MAJOR = 0;
    const MINOR = 1;
    const PATCH = 0;
    const PHASE = 'dev';

    public function __toString() {
        return self::current();
    }

    /**
     * @return bool
     */
    public static function isValid($version) {
        $isValid = false;
        $regexp = '{^
            (\d+)                             # major version
            (\.\d+)?                          # optional minor version
            (\.\d+)?                          # optional patch version
            (-(((alpha|beta|rc)\d*)|dev))?    # optional phase
        $}sx';
        if (preg_match($regexp, $version, $m)) {
            $isValid = true;
        }

        return $isValid;
    }

    public static function current() {
        $callback = function ($arg) {
            if ($arg === null) {
                return false;
            }

            return true;
        };

        $suffix = self::PHASE;

        return implode(
            '.',
            array_filter(
                array(
                    self::MAJOR,
                    self::MINOR,
                    self::PATCH,
                ),
                $callback
            )
        )
        . (empty($suffix) ? '' : '-' . $suffix);
    }
}
