<?php
declare(strict_types = 1);

namespace Morpho\Base;

const INT_TYPE      = 'int';
const FLOAT_TYPE    = 'float';
const BOOL_TYPE     = 'bool';
const STRING_TYPE   = 'string';
const NULL_TYPE     = 'null';
const ARRAY_TYPE    = 'array';
const RESOURCE_TYPE = 'resource';

const TRIM_CHARS = " \t\n\r\x00\x0B";
const EOL_REGEXP = '~(?>\r\n|\n|\r)~s';
const INDENT = '    ';

const SHORTEN_TAIL = '...';
const SHORTEN_LENGTH = 30;

// @TODO: Detect precise value.
// Can be used in comparison operations with real numbers.
const EPS = 0.00001;

function unpackArgs(array $args): array {
    return count($args) === 1 && is_array($args[0])
        ? $args[0]
        : $args;
}

function all(callable $predicate, iterable $it): bool {
    foreach ($it as $key => $value) {
        if (!$predicate($value, $key)) {
            return false;
        }
    }
    return true;
}

function any(callable $predicate, iterable $it): bool {
    foreach ($it as $key => $value) {
        if ($predicate($value, $key)) {
            return true;
        }
    }
    return false;
}

/**
 * Array filter with changed/fixed order of arguments.
 */
function filter(callable $fn, array $arr, bool $resetKeys = true, int $flags = 0): array {
    $arr = array_filter($arr, $fn, $flags);
    return $resetKeys ? array_values($arr) : $arr;
}

/**
 * $fn has type (mixed $prev, mixed $cur): mixed
 */
function reduce(callable $fn, array $arr, $initial = null) {
    return array_reduce($arr, $fn, $initial);
}

function wrap($string, string $wrapper) {
    if (is_array($string)) {
        $r = [];
        foreach ($string as $k => $s) {
            $r[$k] = $wrapper . $s . $wrapper;
        }
        return $r;
    }
    return $wrapper . $string . $wrapper;
}

function wrapQ($string) {
    return wrap($string, "'");
}

function showLn(...$messages) {
    if (!count($messages)) {
        echo "\n";
    } else {
        foreach ($messages as $message) {
            if ($message instanceof \Closure) {
                foreach ($message() as $msg) {
                    echo $msg . "\n";
                }
            } elseif (is_iterable($message)) {
                foreach ($message as $msg) {
                    echo $msg . "\n";
                }
            } else {
                echo $message . "\n";
            }
        }
    }
}

function htmlId($id): string {
    static $htmlIds = [];
    $id = dasherize(deleteDups(preg_replace('/[^\w-]/s', '-', (string)$id), '-'));
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
 * @param string $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 * @param bool   $trim   Either trailing '-' characters should be removed or not.
 *
 * @return string
 */
function dasherize($string, bool $trim = true) {
    $string = sanitize($string, '-_ ');
    $search = ['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'];
    $replace = ['\\1-\\2', '\\1-\\2'];
    $result = strtolower(
        preg_replace(
            $search,
            $replace,
            str_replace(
                ['_', ' '],
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
 * @param string $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 * @param bool   $trim   Either trailing '_' characters should be removed or not.
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
                ['-', ' '],
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
 * @param string $string Allowed string are: /[a-zA-Z0-9_- /\\\\]/s.
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
    $string = str_replace(['-', '_'], ' ', $string);
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
 * @param string $string Allowed string are: /[a-zA-Z0-9_- ]/s.
 *                       All other characters will be removed.
 *
 * @return string
 */
function camelize($string, bool $lcfirst = false): string {
    $string = sanitize($string, '-_ ');
    $string = str_replace(['-', '_'], ' ', $string);
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
 * @param string $string
 * @param bool   $ucwords If == true -> all words will be titleized, else only first word will
 *                        titleized.
 * @param bool   $escape  Either need to apply escaping of HTML special chars?
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
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
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
function trimMore($string, $charlist = null) {
    if (is_array($string)) {
        foreach ($string as $k => $v) {
            $string[$k] = trimMore($v, $charlist);
        }
        return $string;
    }
    return trim((string)$string, $charlist . TRIM_CHARS);
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
 * @param string|int $string Source string with duplicated characters.
 * @param string $chars Either a set of characters to use in character class or a reg-exp pattern that must match
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
    $res = preg_replace(EOL_REGEXP, "\n", $s);
    if (null === $res) {
        throw new \RuntimeException("Unable to replace EOL");
    }
    return $res;
}

/**
 * @param mixed $data
 */
function toJson($data, $options = null): string {
    return json_encode($data, $options ?: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * @return mixed
 */
function fromJson(string $json, bool $objectsToArrays = true) {
    $res = json_decode($json, $objectsToArrays);
    if (null === $res) {
        throw new \RuntimeException("Invalid JSON or too deep data");
    }
    return $res;
}

function endsWith($string, $suffix): bool {
    return substr($string, -strlen($suffix)) === $suffix;
}

function startsWith($string, $prefix): bool {
    // @TODO: Use substr() as for endsWith?
    return 0 === strpos($string, $prefix);
}

function typeOf($val): string {
    if (is_object($val)) {
        return get_class($val);
    }
    $type = gettype($val);
    // @TODO: add void, iterable, callable??
    switch (strtolower($type)) {
        case 'int':
        case 'integer':
            return INT_TYPE;
        case 'float':
        case 'double':
        case 'real':
            return FLOAT_TYPE;
        case 'bool':
        case 'boolean':
            return BOOL_TYPE;
        case 'string':
            return STRING_TYPE;
        case 'null':
            return NULL_TYPE;
        case 'array':
            return ARRAY_TYPE;
        case 'resource':
            return RESOURCE_TYPE;
        default:
            throw new \UnexpectedValueException("Unexpected value of type: '$type'");
    }
}

function buffer(callable $fn): string {
    ob_start();
    try {
        $fn();
    } catch (\Throwable $e) {
        // Don't output any result in case of Error
        ob_end_clean();
        throw $e;
    }
    return ob_get_clean();
}

function prependFn(string $prefix): \Closure {
    return function (string $s) use ($prefix) {
        return $prefix . $s;
    };
}

function appendFn(string $suffix): \Closure {
    return function (string $s) use ($suffix) {
        return $s . $suffix;
    };
}

function partialFn(callable $fn, ...$args1): \Closure {
    return function (...$args2) use ($fn, $args1) {
        return $fn(...array_merge($args1, $args2));
    };
}

/**
 * @return mixed
 */
function requireFile(string $__filePath) {
    return require $__filePath;
}