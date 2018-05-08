<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code;

use Morpho\Base\NotImplementedException;

class Code {
    public static function format(string $php): string {
        throw new NotImplementedException();
    }

    public static function varToStr($var, bool $stripNumericKeys = true): string {
        // @TODO: Replace with Formatter::format().
        $php = \preg_replace(
                [
                    '~=>\s+array~si',
                    '~array \(~si',
                ],
                [
                    '=> array',
                    'array(',
                ],
                \var_export($var, true)
            ) . ';';

        if ($stripNumericKeys) {
            $php = \preg_replace('~^(\s+)\d+.*=> ~mi', '\\1', $php);
        }

        // Reindent code: replace 2 spaces -> 4 spaces.
        $php = \preg_replace_callback(
            '~^\s+~m',
            function ($match) {
                $count = \substr_count($match[0], '  ');
                return \str_repeat('  ', $count * 2);
            },
            $php
        );

        return $php;
    }

    public static function stripComments(string $source): string {
        $output = '';
        foreach (\token_get_all($source) as $token) {
            if (\is_string($token)) {
                $output .= $token;
            } elseif (!\in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a newline
        $output = \preg_replace(['/\s+$/Sm', '/\n+/S'], "\n", $output);

        return $output;
    }
}
