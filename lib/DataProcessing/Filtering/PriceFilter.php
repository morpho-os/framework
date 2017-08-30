<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Filtering;

class PriceFilter extends Filter {
    public function filter($value) {
        if (!is_scalar($value)) {
            return null;
        }
        $value = str_replace(',', '.', $value);
        $search = [
            '{\.+}si',
            '{[^-\d.]}si',
        ];
        $replace = [
            '.',
            '',
        ];
        $value = preg_replace($search, $replace, $value);
        if (!strlen($value) || !self::isFloat($value)) {
            return null;
        }

        return floatval($value);
    }

    private static function isFloat($value) {
        // @TODO: ['+'|'-'] [digit* '.'] digit+ [('e'|'E') ['+'|'-'] digit+]
        return (bool)preg_match('{^[-+]?[0-9]+(?:\.[0-9]*)?$}is', $value);
    }
}
