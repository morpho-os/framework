<?php
/**
 * This file use some ideas (or fragments) found at https://github.com/nikic/iter.
 */

declare(strict_types = 1);
namespace Morpho\Base;

use Closure;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

const INT_TYPE      = 'int';
const FLOAT_TYPE    = 'float';
const BOOL_TYPE     = 'bool';
const STRING_TYPE   = 'string';
const NULL_TYPE     = 'null';
const ARRAY_TYPE    = 'array';
const RESOURCE_TYPE = 'resource';

const TRIM_CHARS = " \t\n\r\x00\x0B";
const EOL_RE      = '(?>\r\n|\n|\r)';
const EOL_FULL_RE = '~' . EOL_RE . '~s';
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

function all(callable $predicate, iterable $list): bool {
    foreach ($list as $key => $value) {
        if (!$predicate($value, $key)) {
            return false;
        }
    }
    return true;
}

function any(callable $predicate, iterable $list): bool {
    foreach ($list as $key => $value) {
        if ($predicate($value, $key)) {
            return true;
        }
    }
    return false;
}

function map(callable $fn, $list): iterable {
    foreach ($list as $k => $v) {
        yield $k => $fn($v, $k);
    }
}

/**
 * @return iterable Generator if $list argument is not an array
 */
function filter(callable $predicate, iterable $list): iterable {
    foreach ($list as $k => $v) {
        if ($predicate($v, $k)) {
            yield $k => $v;
        }
    }
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
            if ($message instanceof Closure) {
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
    $string = sanitize($string, '-_ ', false);
    $string = deleteDups($string, '_ ');
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
    $string = sanitize($string, '-_ ', false);
    $string = deleteDups($string, '- ');
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

function sanitize(string $string, string $allowedCharacters, bool $deleteDups = true) {
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

function head($list, string $separator = null) {
    if (is_array($list)) {
        if (!count($list)) {
            throw new \RuntimeException('Empty list');
        }
        return array_shift($list);
    } elseif (is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        if (null === $separator) {
            return substr($list, 0, 1);
        }
        $pos = strpos($list, $separator);
        return false === $pos
            ? $list
            : substr($list, 0, $pos);
    } else {
        $empty = true;
        $head = null;
        foreach ($list as $v) {
            $empty = false;
            $head = $v;
            break;
        }
        if ($empty) {
            throw new \RuntimeException('Empty list');
        }
        return $head;
    }
}

function last($list, string $separator = null) {
    if (is_array($list)) {
        if (!count($list)) {
            throw new \RuntimeException('Empty list');
        }
        return array_pop($list);
    } elseif (is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        if (null === $separator) {
            return substr($list, -1);
        }
        $pos = strrpos($list, $separator);
        return false === $pos
            ? $list
            : substr($list, $pos + 1);
    } else {
        $empty = true;
        $last = null;
        foreach ($list as $v) {
            $empty = false;
            $last = $v;
        }
        if ($empty) {
            throw new \RuntimeException('Empty list');
        }
        return $last;
    }
}

function init($list, string $separator = null) {
    if (is_array($list)) {
        if (!count($list)) {
            throw new \RuntimeException('Empty list');
        }
        throw new NotImplementedException();
    } elseif (is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        /*
        $parts = explode($separator, $list);
        array_pop($parts);
        return implode('\\', $parts);
        */
        // @TODO, mb_substr()
        $pos = strrpos($list, $separator);
        return false === $pos
            ? ''
            : substr($list, 0, $pos);
    } else {
        $empty = true;
        foreach ($list as $v) {
            $empty = false;
        }
        if ($empty) {
            throw new \RuntimeException('Empty list');
        }
        throw new NotImplementedException();
    }
}

function tail($list, string $separator = null) {
    if (is_array($list)) {
        if (!count($list)) {
            throw new \RuntimeException('Empty list');
        }
        throw new NotImplementedException();
    } elseif (is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        $pos = strpos($list, $separator);
        return false === $pos
            ? ''
            : substr($list, $pos + 1);
    } else {
        $empty = true;
        $gen = function () use ($list, &$empty) {
            foreach ($list as $v) {
                if ($empty) {
                    $empty = false;
                } else {
                    yield $v;
                }
            }
            if ($empty) {
                throw new \RuntimeException('Empty list');
            }
        };
        return $gen();
    }
}

/**
 * Removes duplicated characters from the string.
 *
 * @param string|int $string Source string with duplicated characters.
 * @param string|int $chars Either a set of characters to use in character class or a reg-exp pattern that must match
 *                               all duplicated characters that must be removed.
 * @return string                String with removed duplicates.
 */
function deleteDups($string, $chars, bool $isCharClass = true) {
    $regExp = $isCharClass
        ? '/([' . preg_quote((string)$chars, '/') . '])+/si'
        : "/($chars)+/si";

    return preg_replace($regExp, '\1', (string)$string);
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
    $res = preg_replace(EOL_FULL_RE, "\n", $s);
    if (null === $res) {
        throw new RuntimeException("Unable to replace EOL");
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
        throw new RuntimeException("Invalid JSON or too deep data");
    }
    return $res;
}

function endsWith($string, $suffix): bool {
    return substr($string, -strlen($suffix)) === $suffix;
}

function startsWith($string, $prefix): bool {
    if ($prefix === '') {
        return true;
    }
    return 0 === strpos($string, $prefix);
}

function contains($haystack, $needle): bool {
    if (is_string($haystack)) {
        if ($needle === '') {
            return true;
        }
        //mb_strpos() ??
        return false !== strpos($haystack, $needle);
    } elseif (is_array($haystack)) {
        return in_array($needle, $haystack, true);
    } else {
        // @TODO: iterable
        throw new NotImplementedException();
    }
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
            throw new UnexpectedValueException("Unexpected value of type: '$type'");
    }
}

function buffer(callable $fn): string {
    ob_start();
    try {
        $fn();
    } catch (Throwable $e) {
        // Don't output any result in case of Error
        ob_end_clean();
        throw $e;
    }
    return ob_get_clean();
}

function prepend(array $it, string $prefix) {
    // @TODO: iterable
    return array_map(prefixFn($prefix), $it);
}

function append(array $it, string $suffix) {
    // @TODO: iterable
    return array_map(suffixFn($suffix), $it);
}

function prefixFn(string $prefix): Closure {
    return function (string $s) use ($prefix) {
        return $prefix . $s;
    };
}

function suffixFn(string $suffix): Closure {
    return function (string $s) use ($suffix) {
        return $s . $suffix;
    };
}

function notFn(callable $predicateFn): Closure {
    return function (...$args) use ($predicateFn) {
        return !$predicateFn(...$args);
    };
}

function hasPrefixFn(string $prefix): Closure {
    return function ($s) use ($prefix) {
        return startsWith($s, $prefix);
    };
}

function hasSuffixFn(string $suffix): Closure {
    return function ($s) use ($suffix) {
        return endsWith($s, $suffix);
    };
}

function partialFn(callable $fn, ...$args1): Closure {
    return function (...$args2) use ($fn, $args1) {
        return $fn(...array_merge($args1, $args2));
    };
}

function composeFn(callable $f, callable $g): Closure {
    return function ($v) use ($f, $g) {
        return $f($g($v));
    };
}

/**
 * @return mixed
 */
function requireFile(string $__filePath) {
    return require $__filePath;
}

function toArray($arrOrTraversable, bool $useKeys = false): array {
    return is_array($arrOrTraversable)
        ? $arrOrTraversable
        : iterator_to_array($arrOrTraversable, $useKeys);
}

// @TODO: Move to Byte??, merge with Converter

function formatBytes(string $bytes, string $format = null): string {
    $n = strlen($bytes);
    $s = '';
    $format = $format ?: '\x%02x';
    for ($i = 0; $i < $n; $i++) {
        $s .= sprintf($format, ord($bytes[$i]));
    }
    return $s;
}
