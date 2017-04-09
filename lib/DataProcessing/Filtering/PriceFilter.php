<?php
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
