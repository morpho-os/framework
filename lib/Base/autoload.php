<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use Closure;
use Morpho\Web\View\Html;
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

const WAIT_INTERVAL_MICRO_SEC = 200000;

function unpackArgs(array $args): array {
    return \count($args) === 1 && \is_array($args[0])
        ? $args[0]
        : $args;
}

function wrap($string, string $wrapper) {
    if (\is_array($string)) {
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
    if (!\count($messages)) {
        echo "\n";
    } else {
        foreach ($messages as $message) {
            if ($message instanceof Closure) {
                foreach ($message() as $msg) {
                    echo $msg . "\n";
                }
            } elseif (\is_iterable($message)) {
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
    $id = dasherize(deleteDups(\preg_replace('/[^\w-]/s', '-', (string)$id), '-'));
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
    $result = \strtolower(
        \preg_replace(
            $search,
            $replace,
            \str_replace(
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
    $result = \strtolower(
        \preg_replace(
            '~([a-z])([A-Z])~s',
            '$1_$2',
            \str_replace(
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
    $string = sanitize(\str_replace('/', '\\', $string), '-_\\ ');
    if (false !== \strpos($string, '\\')) {
        $string = \preg_replace_callback(
            '{\\\\(\w)}si',
            function ($match) {
                return '\\' . \strtoupper($match[1]);
            },
            $string
        );
    }
    $string = \str_replace(['-', '_'], ' ', $string);
    $string = \ucwords($string);
    $string = \str_replace(' ', '', $string);
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
    $string = \str_replace(['-', '_'], ' ', $string);
    $string = \ucwords($string);
    $string = \str_replace(' ', '', $string);
    if (!$lcfirst) {
        return \lcfirst($string);
    }

    return $string;
}

/**
 * Replaces the '_' character with space, works for camelCased strings also:
 * 'camelCased' -> 'camel cased'. Leaves other characters as is.
 * By default applies Html::encode() method to escape of HTML special characters.
 */
function humanize($string, bool $escape = true) {
    $result = \preg_replace_callback(
        '/([a-z])([A-Z])/s',
        function ($m) {
            return $m[1] . ' ' . \strtolower($m[2]);
        },
        \str_replace('_', ' ', $string)
    );

    if ($escape) {
        $result = Html::encode($result);
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
        return \ucwords($result);
    }

    return \ucfirst($result);
}

function sanitize(string $string, string $allowedCharacters, bool $deleteDups = true) {
    $regexp = '/[^a-zA-Z0-9' . \preg_quote($allowedCharacters, '/') . ']/s';
    $result = \preg_replace($regexp, '', $string);
    if ($deleteDups) {
        $result = deleteDups($result, $allowedCharacters);
    }

    return $result;
}

/**
 * Modified version of \trim() that removes all characters from the
 * charlist until non of them will be present in the ends of the source string.
 *
 * @param string|array $string
 * @param $charlist
 *
 * @return string|array
 */
function trimMore($string, $charlist = null) {
    if (\is_array($string)) {
        foreach ($string as $k => $v) {
            $string[$k] = trimMore($v, $charlist);
        }
        return $string;
    }
    return \trim((string)$string, $charlist . TRIM_CHARS);
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
        ? '/([' . \preg_quote((string)$chars, '/') . '])+/si'
        : "/($chars)+/si";
    return \preg_replace($regExp, '\1', (string)$string);
}

function filterStringArgs($string, array $args, callable $filterFn): string {
    $fromToMap = [];
    foreach ($args as $key => $value) {
        $fromToMap['{' . $key . '}'] = $filterFn($value);
    }
    return \strtr($string, $fromToMap);
}

function shorten(string $text, int $length = SHORTEN_LENGTH, $tail = null): string {
    if (\strlen($text) <= $length) {
        return $text;
    }
    if (null === $tail) {
        $tail = SHORTEN_TAIL;
    }
    return \substr($text, 0, $length - \strlen($tail)) . $tail;
}

function normalizeEols(string $s): string {
    $res = \preg_replace(EOL_FULL_RE, "\n", $s);
    if (null === $res) {
        throw new RuntimeException("Unable to replace EOL");
    }
    return $res;
}

/**
 * @param mixed $data
 */
function toJson($data, $config = null): string {
    return \json_encode($data, $config ?: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * @return mixed
 */
function fromJson(string $json, bool $objectsToArrays = true) {
    $res = \json_decode($json, $objectsToArrays);
    if (null === $res) {
        throw new RuntimeException("Invalid JSON or too deep data");
    }
    return $res;
}

function endsWith(string $string, string $suffix): bool {
    if ($suffix === '') {
        return true;
    }
    return \substr($string, -\strlen($suffix)) === $suffix;
}

function startsWith(string $string, string $prefix): bool {
    if ($prefix === '') {
        return true;
    }
    return 0 === \strpos($string, $prefix);
}

/**
 * Inspired by the lines function in Haskell.
 */
function lines(string $text): array {
/*    if ($text === '') {
        return [];
    }*/
    return \preg_split(EOL_FULL_RE, $text);
}

// @TODO: implement nonEmptyLines()

function typeOf($val): string {
    if (\is_object($val)) {
        return \get_class($val);
    }
    $type = \gettype($val);
    // @TODO: add void, iterable, callable??
    switch (\strtolower($type)) {
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

function capture(callable $fn): string {
    \ob_start();
    try {
        $fn();
    } catch (Throwable $e) {
        // Don't output any result in case of Error
        \ob_end_clean();
        throw $e;
    }
    return \ob_get_clean();
}

function tpl($__filePath, array $__vars): string {
    \extract($__vars, EXTR_SKIP);
    unset($__vars);
    \ob_start();
    try {
        require $__filePath;
    } catch (\Throwable $e) {
        // Don't output any result in case of Error
        \ob_end_clean();
        throw $e;
    }
    return \trim(\ob_get_clean());
}

function prefix(string $prefix): Closure {
    return function (string $s) use ($prefix) {
        return $prefix . $s;
    };
}

function suffix(string $suffix): Closure {
    return function (string $s) use ($suffix) {
        return $s . $suffix;
    };
}

/**
 * Modified version of the operator() from the https://github.com/nikic/iter
 * @Copyright (c) 2013 by Nikita Popov.
 */
function op($operator, $arg = null): \Closure {
    $functions = [
        'instanceof' => function ($a, $b) { return $a instanceof $b; },
        '*'          => function ($a, $b) { return $a * $b; },
        '/'          => function ($a, $b) { return $a / $b; },
        '%'          => function ($a, $b) { return $a % $b; },
        '+'          => function ($a, $b) { return $a + $b; },
        '-'          => function ($a, $b) { return $a - $b; },
        '.'          => function ($a, $b) { return $a . $b; },
        '<<'         => function ($a, $b) { return $a << $b; },
        '>>'         => function ($a, $b) { return $a >> $b; },
        '<'          => function ($a, $b) { return $a < $b; },
        '<='         => function ($a, $b) { return $a <= $b; },
        '>'          => function ($a, $b) { return $a > $b; },
        '>='         => function ($a, $b) { return $a >= $b; },
        '=='         => function ($a, $b) { return $a == $b; },
        '!='         => function ($a, $b) { return $a != $b; },
        '==='        => function ($a, $b) { return $a === $b; },
        '!=='        => function ($a, $b) { return $a !== $b; },
        '&'          => function ($a, $b) { return $a & $b; },
        '^'          => function ($a, $b) { return $a ^ $b; },
        '|'          => function ($a, $b) { return $a | $b; },
        '&&'         => function ($a, $b) { return $a && $b; },
        '||'         => function ($a, $b) { return $a || $b; },
        '**'         => function ($a, $b) { return \pow($a, $b); },
        '<=>'        => function ($a, $b) { return $a == $b ? 0 : ($a < $b ? -1 : 1); },
    ];

    if (!isset($functions[$operator])) {
        throw new \InvalidArgumentException("Unknown operator \"$operator\"");
    }

    $fn = $functions[$operator];
    if (\func_num_args() === 1) {
        // Return a function which expects 2 arguments.
        return $fn;
    } else {
        // Capture the first argument of the binary operator, return a function which expect the second one (currying).
        return function($a) use ($fn, $arg) {
            return $fn($a, $arg);
        };
    }
}

function not(callable $predicateFn): Closure {
    return function (...$args) use ($predicateFn) {
        return !$predicateFn(...$args);
    };
}

function hasPrefix(string $prefix): Closure {
    return function ($s) use ($prefix) {
        return startsWith($s, $prefix);
    };
}

function hasSuffix(string $suffix): Closure {
    return function ($s) use ($suffix) {
        return endsWith($s, $suffix);
    };
}

function partial(callable $fn, ...$args1): Closure {
    return function (...$args2) use ($fn, $args1) {
        return $fn(...\array_merge($args1, $args2));
    };
}

/**
 * Returns a new function which will call $f after $g (f . g). Input of a $g, will be input argument of the function and return value of the $f will be output of the function: function (InputTypeOfG $inputOfG): OutputTypeOfF {...}
 */
function compose(callable $f, callable $g): Closure {
    return function ($v) use ($f, $g) {
        return $f($g($v));
    };
}

/**
 * @return mixed
 */
function requireFile(string $__filePath, bool $__once = false) {
    if ($__once) {
        return require_once $__filePath;
    }
    return require $__filePath;
}

// @TODO: Move to Byte??, merge with Converter

function formatBytes(string $bytes, string $format = null): string {
    $n = \strlen($bytes);
    $s = '';
    $format = $format ?: '\x%02x';
    for ($i = 0; $i < $n; $i++) {
        $s .= \sprintf($format, \ord($bytes[$i]));
    }
    return $s;
}

function formatFloat($val): string {
    if (empty($val)) {
        $val = 0;
    }
    $val = \str_replace(',', '.', $val);
    return \number_format(\round(\floatval($val), 2), 2, '.', ' ');
}

function hash($var): string {
    // @TODO: Use it in memoize, check all available types.
    throw new NotImplementedException();
}

function equals($a, $b) {
    throw new NotImplementedException();
}

/**
 * @TODO: This method can't reliable say when a function is called with different arguments.
 */
function memoize(callable $fn): \Closure {
    return function (...$args) use ($fn) {
        static $memo = [];
/*
        $hash = array_reduce($args, function ($acc, $var) {
            $hash = '';
            if (\is_object($var)) {
                $hash .= spl_object_hash($var);
            } elseif (is_scalar($var)) { //  int, float, string and bool
            return $hash;
        });
*/
        // @TODO: avoid overwritting different functions called with the same arguments.
        $hash = \md5(\json_encode($args)); // NB: \md5() can cause collisions
        if (\array_key_exists($hash, $memo)) {
            return $memo[$hash];
        }
        return $memo[$hash] = $fn(...$args);
    };
}

function waitUntilNoOfAttempts(callable $predicate, int $waitIntervalMicroSec = null, int $noOfAttempts = 30): void {
    if (null === $waitIntervalMicroSec) {
        $waitIntervalMicroSec = WAIT_INTERVAL_MICRO_SEC;
    }
    for ($i = 0; $i < $noOfAttempts; $i++) {
        if ($predicate()) {
            return;
        }
        \usleep($waitIntervalMicroSec);
    }
    throw new \RuntimeException('The condition is not satisfied');
}

function waitUntilTimeout(callable $predicate, int $timeoutMicroSec) {
    // @TODO: use waitUntilNoOfAttempts
    throw new NotImplementedException();
}

// ----------------------------------------------------------------------------
// Iterables
// Code below based on the https://github.com/nikic/iter (Copyright (c) 2013 by Nikita Popov)
// Functions are ordered by name.

/**
 * @param string|iterable $iter
 */
function all(callable $predicate, $iter): bool {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
        return true;
    }
    foreach ($iter as $key => $value) {
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

function append(array $it, string $suffix) {
    // @TODO: iterable
    return \array_map(suffix($suffix), $it);
}

function apply(callable $fn, $iter): void {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
    } else {
        foreach ($iter as $k => $v) {
            $fn($v, $k);
        }
    }
}

/**
 * Modified version from the https://github.com/nikic/iter
 * @Copyright (c) 2013 by Nikita Popov.
 *
 * Chains the iterables that were passed as arguments.
 *
 * The resulting iterator will contain the values of the first iterable, then the second, and so on.
 *
 * Example:
 *     chain(range(0, 5), range(6, 10), range(11, 15))
 *     => iterable(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15)
 */
function chain(...$iterables): iterable {
    // @TODO: Handle strings
    //_assertAllIterable($iterables);
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }
}

/**
 * @param iterable|string $haystack
 * @param mixed $needle
 */
function contains($haystack, $needle): bool {
    if (\is_string($haystack)) {
        if ($needle === '') {
            return true;
        }
        //mb_strpos() ??
        return false !== \strpos($haystack, $needle);
    } elseif (\is_array($haystack)) {
        return \in_array($needle, $haystack, true);
    } else {
        // @TODO: iterable
        throw new NotImplementedException();
    }
}

/**
 * @param string|iterable $iter
 * @return string|\Generator|array
 *     string if $list : string
 *     array if $list : array
 *     Generator otherwise
 */
function filter(callable $predicate, $iter) {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
        return '';
    }
    if (\is_array($iter)) {
        $res = [];
        $numericKeys = true;
        foreach ($iter as $k => $v) {
            if ($numericKeys && !\is_numeric($k)) {
                $numericKeys = false;
            }
            if ($predicate($v, $k)) {
                $res[$k] = $v;
            }
        }
        return $numericKeys ? \array_values($res) : $res;
    } else {
        return (function () use ($predicate, $iter) {
            foreach ($iter as $k => $v) {
                if ($predicate($v, $k)) {
                    yield $k => $v;
                }
            }
        })();
    }
}

/**
 * Modified version from the https://github.com/nikic/iter
 * @Copyright (c) 2013 by Nikita Popov.
 *
 * Applies a function to each value in an iterator and flattens the result.
 *
 * The function is passed the current iterator value and should return an
 * iterator of new values. The result will be a concatenation of the iterators
 * returned by the mapping function.
 *
 * Examples
 *     flatMap(function($v) { return [-$v, $v]; }, [1, 2, 3, 4, 5]);
 *     => iterable(-1, 1, -2, 2, -3, 3, -4, 4, -5, 5)
 *
 * @param callable $function Mapping function: iterable function(mixed $value)
 * @param iterable|string $iterable Iterable to be mapped over
 *
 * @return string|\Generator|array
 */
function flatMap(callable $fn, $iter) {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
        return '';
    }
    if (\is_array($iter)) {
        $newArr = [];
        foreach ($iter as $value) {
            foreach ($fn($value) as $k => $v) {
                $newArr[$k] = $v;
            }
        }
        return $newArr;
    }
    // @TODO: Handle strings
    return (function () use ($fn, $iter) {
        foreach ($iter as $value) {
            foreach ($fn($value) as $k => $v) {
                yield $k => $v;
            }
        }
    })();
}

/**
 * For abcd returns a
 */
function head($list, string $separator = null) {
    if (\is_array($list)) {
        if (!\count($list)) {
            throw new \RuntimeException('Empty list');
        }
        return \array_shift($list);
    } elseif (\is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        if (null === $separator) {
            return \substr($list, 0, 1);
        }
        $pos = \strpos($list, $separator);
        return false === $pos
            ? $list
            : \substr($list, 0, $pos);
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

/**
 * For abcd returns abc
 */
function init($list, string $separator = null) {
    if (\is_array($list)) {
        if (!\count($list)) {
            throw new \RuntimeException('Empty list');
        }
        return \array_slice($list, 0, -1, true);
    } elseif (\is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        /*
        $parts = explode($separator, $list);
        \array_pop($parts);
        return implode('\\', $parts);
        */
        // @TODO, mb_substr()
        $pos = \strrpos($list, $separator);
        return false === $pos
            ? ''
            : \substr($list, 0, $pos);
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

/**
 * For abcd returns d
 */
function last($list, string $separator = null) {
    if (\is_array($list)) {
        if (!\count($list)) {
            throw new \RuntimeException('Empty list');
        }
        return \array_pop($list);
    } elseif (\is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        if (null === $separator) {
            return \substr($list, -1);
        }
        $pos = \strrpos($list, $separator);
        return false === $pos
            ? $list
            : \substr($list, $pos + 1);
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

/**
 * @return string|\Generator|array
 */
function map(callable $fn, $iter) {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
        return '';
    }
    if (\is_array($iter)) {
        $newArr = [];
        foreach ($iter as $k => $v) {
            $newArr[$k] = $fn($v, $k);
        }
        return $newArr;
    }
    // @TODO: Handle strings
    return (function () use ($fn, $iter) {
        foreach ($iter as $k => $v) {
            yield $k => $fn($v, $k);
        }
    })();
}

function prepend(array $it, string $prefix): array {
    // @TODO: iterable
    return \array_map(prefix($prefix), $it);
}

/**
 * Modified version from the https://github.com/nikic/iter
 * @Copyright (c) 2013 by Nikita Popov.
 *
 * Reduce iterable $iter using a function $fn into a single value.
 * The `reduce` function also known as the `fold`.
 *
 * Examples:
 *      reduce(op('+'), range(1, 5), 0)
 *      => 15
 *      reduce(op('*'), range(1, 5), 1)
 *      => 120
 *
 * @param callable $function Reduction function: (mixed $acc, mixed $curValue, mixed $curKey)
 *     where $acc is the accumulator
 *           $curValue is the current element
 *           $curKey is a key of the current element
 *     The reduction function must return a new accumulator value.
 * @param iterable|string $iter Iterable to reduce.
 * @param mixed $initial Start value for accumulator. Usually identity value of $function.
 *
 * @return mixed Result of the reduction.
 */
function reduce(callable $fn, $iter, $initial = null) {
    if (\is_string($iter)) {
        // @TODO:  array mb_split ( string $pattern , string $string [, int $limit = -1 ] )
        throw new NotImplementedException();
    }
    $acc = $initial;
    foreach ($iter as $key => $value) {
        $acc = $fn($acc, $value, $key);
    }
    return $acc;
}

/**
 * For abcd returns bcd
 */
function tail($list, string $separator = null) {
    if (\is_array($list)) {
        if (!\count($list)) {
            throw new \RuntimeException('Empty list');
        }
        \array_shift($list);
        return $list;
    } elseif (\is_string($list)) {
        if ($list === '') {
            throw new \RuntimeException('Empty list');
        }
        // @TODO, mb_substr()
        $pos = \strpos($list, $separator);
        return false === $pos
            ? ''
            : \substr($list, $pos + 1);
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
 * @param string|iterable iter
 */
function toArray($iter): array {
    if (\is_string($iter)) {
        if ($iter !== '') {
            throw new NotImplementedException();
        }
        return [];
    }
    $arr = [];
    $i = 0;
    foreach ($iter as $key => $value) {
        if (\preg_match('~^\d+$~s', (string)$key)) {
            $arr[$i] = $value;
            $i++;
        } else {
            $arr[$key] = $value;
        }
    }
    return $arr;
}
