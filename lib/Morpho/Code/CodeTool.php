<?php
namespace Morpho\Code;

use Morpho\Fs\File;
use SebastianBergmann\Exporter\Exporter;

class CodeTool {
    public static function exportVar($var, $short = false) {
        $exporter = new Exporter();
        return $short ? $exporter->shortenedExport($var) : $exporter->export($var);
    }

    /**
     * @return string
     */
    public static function varToPhp($var, $filePath = null, $stripNumericKeys = true) {
        $php = preg_replace(
                array(
                    '~=>\s+array~si',
                    '~array \(~si',
                ),
                array(
                    '=> array',
                    'array(',
                ),
                var_export($var, true)
            ) . ';';
        if ($stripNumericKeys) {
            $php = preg_replace('~^(\s+)\d+.*=> ~mi', '\\1', $php);
        }
        // Reindent code: replace 2 spaces -> 4 spaces.
        $php = preg_replace_callback(
            '~^\s+~m',
            function ($match) {
                $count = substr_count($match[0], '  ');
                return str_repeat('  ', $count * 2);
            },
            $php
        );
        if (null !== $filePath) {
            File::write($filePath, "<?php\nreturn " . $php);
        }

        return $php;
    }

    public static function stripComments($source) {
        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a newline
        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

        return $output;
    }
}
