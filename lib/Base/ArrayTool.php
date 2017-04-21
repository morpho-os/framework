<?php
namespace Morpho\Base;

use OutOfBoundsException;
use RuntimeException;
use UnexpectedValueException;

class ArrayTool {
    /**
     * Union for sets, for difference use array_diff(), for intersection use array_intersect().
     */
    public static function union(...$arr): array {
        // @TODO: make it work for array of arrays and other cases.
        return array_unique(array_merge(...$arr));
    }

    public static function intersect(...$arr): array {
        return array_intersect_key(...$arr);
    }

    /**
     * Symmetrical difference of the two sets: ($a \ $b) U ($b \ $a).
     * If for $a[$k1] and $b[$k2] string keys are equal the value $b[$k2] will overwrite the value $a[$k1].
     */
    public static function symmetricDiff(array $a, array $b): array {
        $diffA = array_diff($a, $b);
        $diffB = array_diff($b, $a);
        return self::union($diffA, $diffB);
    }

    public static function cartesianProduct(...$arrs) {
        // @TODO
        throw new NotImplementedException();
    }

    public static function permutations(array $arr, int $n, bool $allowDups = false): array {
        // https://en.wikipedia.org/wiki/Heap%27s_algorithm
        throw new NotImplementedException();
    }

    public static function combinations(array $arr, int $n, bool $allowDups = false): array {
        throw new NotImplementedException();
    }

    /**
     * Returns set with power 2^count($arr)
     *
     * of all subsets,the number of elements of the output is 2^count($arr).
     * The $arr must be either empty or non-empty and have numeric keys.
     */
    public static function subsets(array $arr): array {
        if (count($arr) > (8 * PHP_INT_SIZE)) {
            throw new OutOfBoundsException('Too large array/set, max number of elements of the input can be ' . (8 * PHP_INT_SIZE));
        }
        $subsets = [];
        $n = count($arr);
        // Original algo is written by Brahmananda (https://www.quora.com/How-do-I-generate-all-subsets-of-a-set-in-Java-iteratively)
        // 1 << count($arr) is 2^n - the number of all subsets.
        for ($i = 0; $i < (1 << $n); $i++) {
            $subsetBits = $i;
            $subset = [];
            for ($j = 0; $j < $n; $j++) { // $n is the width of the bit field, number of elements in the input set.
                if ($subsetBits & 1) {  // is the right bit is 1?
                    $subset[] = $arr[$j];
                }
                $subsetBits = $subsetBits >> 1; // process next bit
             }
             $subsets[] = $subset;
        }
        return $subsets;
    }

    public static function isSubset(array $a, array $b): bool {
        return self::intersect($a, $b) == $b;
    }

    /**
     * Compares sets not strictly. Each element of each array must be scalar.
     * @return bool
     */
    public static function setsEqual(array $a, array $b): bool {
        return count($a) === count($b) && count(array_diff($a, $b)) === 0;
    }

    public static function itemsWithKeys(array $arr, array $keys): array {
        return self::intersect($arr, array_flip(array_values($keys)));
    }

    public static function flatten(array $arr): array {
        $result = [];
        foreach ($arr as $val) {
            if (is_array($val)) {
                $result = array_merge($result, self::flatten($val));
            } else {
                $result[] = $val;
            }
        }
        return $result;
    }

    public static function head(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new UnexpectedValueException("Empty list");
        }
        return array_shift($list);
    }

    public static function tail(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new UnexpectedValueException("Empty list");
        }
        array_shift($list);
        return $list;
    }

    public static function last(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new UnexpectedValueException("Empty list");
        }
        return array_pop($list);
    }

    public static function init(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new UnexpectedValueException("Empty list");
        }
        return array_slice($list, 0, -1, true);
    }

    public static function toKeyed(array $matrix, $keyForIndex, bool $drop = false): array {
        $result = [];
        foreach ($matrix as $row) {
            if (!isset($row[$keyForIndex])) {
                throw new RuntimeException();
            }
            $k = $row[$keyForIndex];
            if ($drop) {
                unset($row[$keyForIndex]);
            }
            $result[$k] = $row;
        }
        return $result;
    }

    public static function camelizeKeys(array $array): array {
        $result = [];
        foreach ($array as $key => $value) {
            $result[camelize($key)] = $value;
        }

        return $result;
    }

    public static function underscoreKeys(array $array): array {
        $result = [];
        foreach ($array as $key => $value) {
            $result[underscore($key)] = $value;
        }

        return $result;
    }

    public static function handleOptions(array $options, array $defaultOptions): array {
        if (null === $options || count($options) === 0) {
            return $defaultOptions;
        }
        $diff = array_diff_key($options, array_flip(array_keys($defaultOptions)));
        if (count($diff)) {
            throw new InvalidOptionsException($diff);
        }
        return array_merge($defaultOptions, $options);
    }

    /**
     * Unsets all items of array with $key recursively.
     */
    public static function unsetRecursive(array &$arr, $key): array {
        unset($arr[$key]);
        foreach (array_keys($arr) as $k) {
            if (is_array($arr[$k])) {
                self::unsetRecursive($arr[$k], $key);
            }
        }

        return $arr;
    }

    public static function unset(array $arr, $val, bool $strict = true): array {
        $key = array_search($val, $arr, $strict);
        if (false !== $key) {
            unset($arr[$key]);
        }
        return $arr;
    }

    public static function hash(array $arr): string {
        return md5(json_encode($arr));
    }
}
