<?php
namespace Morpho\Filter;

class PriceFilter extends Filter {
    public function filter($value) {
        if (!is_scalar($value)) {
            return null;
        }
        $value = str_replace(',', '.', $value);
        $search = array(
            '{\.+}si',
            '{[^-\d.]}si',
        );
        $replace = array(
            '.',
            '',
        );
        $value = preg_replace($search, $replace, $value);
        if (!strlen($value) || !self::isFloat($value)) {
            return null;
        }

        return floatval($value);
    }

    private static function isFloat($value) {
        // ['+'|'-'] [digit* '.'] digit+ [('e'|'E') ['+'|'-'] digit+]
        return (bool)preg_match('{^[-+]?[0-9]+(?:\.[0-9]*)?$}is', $value);
    }
}
