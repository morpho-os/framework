<?php
declare(strict_types=1);

namespace Morpho\Base;

function unpackArgs(array $args): array {
    return count($args) === 1 && is_array($args[0])
        ? $args[0]
        : $args;
}

function all(callable $predicate, array $arr): bool {
    foreach ($arr as $key => $value) {
        if (!$predicate($value, $key)) {
            return false;
        }
    }
    return true;
}

function any(callable $predicate, array $arr): bool {
    foreach ($arr as $key => $value) {
        if ($predicate($value, $key)) {
            return true;
        }
    }
    return false;
}

/**
 * Array filter with changed/fixed order of arguments.
 */
function filter(callable $filter, array $arr, bool $resetKeys = true, int $flags = 0): array {
    $arr = array_filter($arr, $filter, $flags);
    return $resetKeys ? array_values($arr) : $arr;
}

/**
 * $fn has type (mixed $prev, mixed $cur): mixed
 */
function fold(callable $fn, array $arr, $initial = null) {
    return array_reduce($arr, $fn, $initial);
}

function wrap($string, string $wrapper): string {
    return $wrapper . $string . $wrapper;
}

function writeLn(...$messages) {
    echo implode("\n", $messages) . "\n";
}

function htmlId($id) {
    static $htmlIds = [];
    $id = dasherize(deleteDups(preg_replace('/[^\w-]/s', '-', $id), '-'));
    if (isset($htmlIds[$id])) {
        $id .= '-' . $htmlIds[$id]++;
    } else {
        $htmlIds[$id] = 1;
    }

    return $id;
}

/**
 * Generates unique name within single HTTP request.
 */
function uniqueName(): string {
    static $uniqueInt = 0;
    return 'unique' . $uniqueInt++;
}

/**
 * Replaces first capsed letter or underscore with dash and small later.
 *
 * @param $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 * @param $trim Either trailing '-' characters should be removed or not.
 *
 * @return string
 */
function dasherize($string, bool $trim = true) {
    $string = sanitize($string, '-_ ');
    $search = array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/');
    $replace = array('\\1-\\2', '\\1-\\2');
    $result = strtolower(
        preg_replace(
            $search,
            $replace,
            str_replace(
                array('_', ' '),
                '-',
                $string
            )
        )
    );
    if ($trim) {
        return trimMore($result, '-');
    }

    return $result;
}

/**
 * Replaces first capsed letter or dash with underscore and small later.
 *
 * @param $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 * @param $trim Either trailing '_' characters should be removed or not.
 *
 * @return string
 */
function underscore($string, bool $trim = true) {
    $string = sanitize($string, '-_ ');
    $result = strtolower(
        preg_replace(
            '~([a-z])([A-Z])~s',
            '$1_$2',
            str_replace(
                array('-', ' '),
                '_',
                $string
            )
        )
    );
    if ($trim) {
        return trimMore($result, '_');
    }

    return $result;
}

/**
 * Replaces next letter after the allowed character with capital letter.
 * First latter will be always in upper case.
 *
 * @param $string Allowed string are: /[a-zA-Z0-9_- /\\\\]/s.
 *                       All other characters will be removed.
 *                       The '/' will be transformed to '\'.
 *
 * @return string
 */
function classify($string, bool $toFqName = false): string {
    $string = sanitize(str_replace('/', '\\', $string), '-_\\ ');
    if (false !== strpos($string, '\\')) {
        $string = preg_replace_callback(
            '{\\\\(\w)}si',
            function ($match) {
                return '\\' . strtoupper($match[1]);
            },
            $string
        );
    }
    $string = str_replace(array('-', '_'), ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);
    if ($toFqName) {
        return '\\' . $string;
    }

    return $string;
}

/**
 * Replaces next letter after the allowed character with capital letter.
 * First latter will be in upper case if $lcfirst == true or in lower case if $lcfirst == false.
 *
 * @param $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 *
 * @return string
 */
function camelize($string, bool $lcfirst = false): string {
    $string = sanitize($string, '-_ ');
    $string = str_replace(array('-', '_'), ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);
    if (!$lcfirst) {
        return lcfirst($string);
    }

    return $string;
}

/**
 * Replaces the '_' character with space, works for camelCased strings also:
 * 'camelCased' -> 'camel cased'. Leaves other characters as is.
 * By default applies escapeHtml() method to escape of HTML special characters.
 */
function humanize($string, bool $escape = true) {
    $result = preg_replace_callback(
        '/([a-z])([A-Z])/s',
        function ($m) {
            return $m[1] . ' ' . strtolower($m[2]);
        },
        str_replace('_', ' ', $string)
    );

    if ($escape) {
        $result = escapeHtml($result);
    }

    return $result;
}

/**
 * Works like humanize() except makes all words titleized:
 * 'foo bar_baz' -> 'Foo Bar Baz'
 * or only first word:
 * 'foo bar_baz' -> 'Foo bar baz'
 *
 * @param $string
 * @param $ucwords If == true -> all words will be titleized, else only first word will
 *                      titleized.
 * @param $escape Either need to apply escaping of HTML special chars?
 *
 * @return string.
 */
function titleize($string, bool $ucwords = true, bool $escape = true): string {
    $result = humanize($string, $escape);
    if ($ucwords) {
        return ucwords($result);
    }

    return ucfirst($result);
}

function sanitize($string, $allowedCharacters, bool $deleteDups = true) {
    $regexp = '/[^a-zA-Z0-9' . preg_quote($allowedCharacters, '/') . ']/s';
    $result = preg_replace($regexp, '', $string);
    if ($deleteDups) {
        $result = deleteDups($result, $allowedCharacters);
    }

    return $result;
}

function escapeHtml($text): string {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/**
 * Inverts result that can be obtained with escapeHtml().
 */
function unescapeHtml($text): string {
    return htmlspecialchars_decode($text, ENT_QUOTES);
}

/**
 * Modified version of trim() that removes all characters from the
 * charlist until non of them will be present in the ends of the source string.
 *
 * @param string|array $string
 * @param $charlist
 *
 * @return string|array
 */
const TRIM_CHARS = " \t\n\r\x00\x0B";
function trimMore($string, $charlist = null) {
    if (is_array($string)) {
        foreach ($string as $k => $v) {
            $string[$k] = trimMore($v, $charlist);
        }
        return $string;
    }
    return trim((string) $string, $charlist . TRIM_CHARS);
}

function head($string, $separator) {
    // @TODO: Handle arrays too
    $pos = strpos($string, $separator);
    return false === $pos
        ? $string
        : substr($string, 0, $pos);
}

function last($string, $separator) {
    // @TODO: Handle arrays too
    $pos = strrpos($string, $separator);
    return false === $pos
        ? $string
        : substr($string, $pos + 1);
}

function init($string, $separator) {
    // @TODO: Handle arrays too
    $pos = strrpos($string, $separator);
    return false === $pos
        ? $string
        : substr($string, 0, $pos);
}

function tail($string, $separator) {
    // @TODO: Handle arrays too
    throw new NotImplementedException();
}

/**
 * Removes duplicated characters from the string.
 *
 * @param string|int     $string Source string with duplicated characters.
 * @param string         $chars  Either a set of characters to use in character class or a reg-exp pattern that must match
 *                               all duplicated characters that must be removed.
 * @return string                String with removed duplicates.
 */
function deleteDups($string, string $chars, bool $isCharClass = true) {
    $regExp = $isCharClass
        ? '/([' . preg_quote($chars, '/') . '])+/si'
        : "/($chars)+/si";

    return preg_replace($regExp, '\1', $string);
}

function filterStringArgs($string, array $args, callable $filterFn): string {
    $fromToMap = [];
    foreach ($args as $key => $value) {
        $fromToMap['{' . $key . '}'] = $filterFn($value);
    }
    return strtr($string, $fromToMap);
}

const SHORTEN_TAIL = '...';
const SHORTEN_LENGTH = 30;
function shorten(string $text, int $length = SHORTEN_LENGTH, $tail = null): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    if (null === $tail) {
        $tail = SHORTEN_TAIL;
    }
    return substr($text, 0, $length - strlen($tail)) . $tail;
}

function normalizeEols(string $s): string {
    $res = preg_replace('~\r\n?~s', "\n", $s);
    if (null === $res) {
        throw new \RuntimeException("Unable to replace EOL");
    }
    return $res;
}

/**
 * @param mixed $data
 */
function jsonEncode($data): string {
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * @return mixed
 */
function jsonDecode(string $json) {
    return json_decode($json, true);
}