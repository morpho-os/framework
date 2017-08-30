<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class Version {
    protected $major;
    protected $minor;
    protected $patch;
    protected $phase;

    public function __construct(string $major, string $minor = null, string $patch = null, string $phase = null) {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->phase = $phase;
    }

    /**
     * @param string|self $version
     */
    public static function isValid($version): bool {
        $regexp = '{^
            (\d+)                             # major version
            (\.\d+)?                          # optional minor version
            (\.\d+)?                          # optional patch version
            (.*)                              # optional phase
        $}sx';
        $res = (bool) preg_match($regexp, $version, $m);
        return $res;
    }

    public function __toString() {
        $callback = function ($arg) {
            if ($arg === null) {
                return false;
            }

            return true;
        };
        $phase = $this->phase;
        return implode(
            '.',
            array_filter(
                [
                    $this->major,
                    $this->minor,
                    $this->patch,
                ],
                $callback
            )
        ) . (empty($phase) ? '' : '-' . $phase);
    }
}
