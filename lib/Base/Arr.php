<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use OutOfBoundsException;
use RuntimeException;

class Arr {
    /**
     * Union for sets, for difference use array_diff(), for intersection use array_intersect().
     */
    public static function union(...$arr): array {
        // @TODO: make it work for array of arrays and other cases.
        return \array_unique(\array_merge(...$arr));
    }

    public static function intersect(...$arr): array {
        return \array_intersect_key(...$arr);
    }

    /**
     * Symmetrical difference of the two sets: ($a \ $b) U ($b \ $a).
     * If for $a[$k1] and $b[$k2] string keys are equal the value $b[$k2] will overwrite the value $a[$k1].
     */
    public static function symmetricDiff(array $a, array $b): array {
        $diffA = \array_diff($a, $b);
        $diffB = \array_diff($b, $a);
        return self::union($diffA, $diffB);
    }

    public static function cartesianProduct(array $a, array $b) {
        // @TODO: work for iterable
        $res = [];
        foreach ($a as $v1) {
            foreach ($b as $v2) {
                $res[] = [$v1, $v2];
            }
        }
        return $res;
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
        if (\count($arr) > (8 * PHP_INT_SIZE)) {
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

    public static function camelizeKeys(array $arr): array {
        $result = [];
        foreach ($arr as $key => $value) {
            $result[camelize($key)] = $value;
        }

        return $result;
    }

    public static function underscoreKeys(array $arr): array {
        $result = [];
        foreach ($arr as $key => $value) {
            $result[underscore($key)] = $value;
        }

        return $result;
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
