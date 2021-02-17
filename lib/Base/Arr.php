<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use OutOfBoundsException;
use RuntimeException;
use UnexpectedValueException;
use function array_diff;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_merge;
use function array_search;
use function array_unique;
use function array_values;
use function count;
use function is_array;
use function json_encode;
use function md5;

class Arr {
    public static function only(array $arr, array $keys, $createMissingItems = true): array {
        if ($createMissingItems) {
            $newArr = [];
            foreach ($keys as $key) {
                $newArr[$key] = isset($arr[$key]) ? $arr[$key] : null;
            }
            return $newArr;
        }
        return array_intersect_key($arr, array_flip(array_values($keys)));
    }

    public static function require(array $arr, array $requiredKeys, bool $returnOnlyRequired = true, bool $checkForEmptiness = false): array {
        $newArr = [];
        foreach ($requiredKeys as $key) {
            if (!isset($arr[$key]) && !array_key_exists($key, $arr)) {
                throw new UnexpectedValueException("Missing the required item with the key " . $key);
            }
            if ($checkForEmptiness && !$arr[$key]) {
                throw new UnexpectedValueException("The item '$key' is empty");
            }
            $newArr[$key] = $arr[$key];
        }
        return $returnOnlyRequired ? $newArr : $arr;
    }

    /**
     * Union for sets, for difference use \array_diff(), for intersection use \array_intersect().
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
    public static function symmetricDiff(array $arrA, array $arrB): array {
        $diffA = array_diff($arrA, $arrB);
        $diffB = array_diff($arrB, $arrA);
        return self::union($diffA, $diffB);
    }

    public static function cartesianProduct(array $arrA, array $arrB) {
        // @TODO: work for iterable
        $res = [];
        foreach ($arrA as $v1) {
            foreach ($arrB as $v2) {
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
        if (count($arr) > (8 * PHP_INT_SIZE)) {
            throw new OutOfBoundsException('Too large array/set, max number of elements of the input can be ' . (8 * PHP_INT_SIZE));
        }
        $subsets = [];
        $n = count($arr);
        // Original algo is written by Brahmananda (https://www.quora.com/How-do-I-generate-all-subsets-of-a-set-in-Java-iteratively)
        // 1 << \count($arr) is 2^n - the number of all subsets.
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

    public static function isSubset(array $arrA, array $arrB): bool {
        return self::intersect($arrA, $arrB) == $arrB;
    }

    /**
     * Compares sets not strictly. Each element of each array must be scalar.
     * @return bool
     */
    public static function setsEqual(array $arrA, array $arrB): bool {
        return count($arrA) === count($arrB) && count(array_diff($arrA, $arrB)) === 0;
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

    public static function unset(array $arr, $val, bool $strict = true): array {
        $key = array_search($val, $arr, $strict);
        if (false !== $key) {
            unset($arr[$key]);
        }
        return $arr;
    }

    public static function unsetMulti(array $arr, iterable $val, bool $strict = true): array {
        // NB: unsetMulti() can't merged with unset() as $val in unset() can be array, i.e. unset() has to support unsetting arrays.
        foreach ($val as $v) {
            $key = array_search($v, $arr, $strict);
            if (false !== $key) {
                unset($arr[$key]);
            }
        }
        return $arr;
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

    public static function hash(array $arr): string {
        return md5(json_encode($arr));
    }

    /**
     * Modified \Zend\Stdlib\ArrayUtils::merge() from the http://github.com/zendframework/zf2
     *
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays and preserveNumericKeys is false, the value
     * from the second array will be appended to the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the one of the first array.
     */
    public static function merge(array $arrA, array $arrB, bool $preserveNumericKeys = false): array {
        foreach ($arrB as $key => $value) {
            if (isset($arrA[$key]) || array_key_exists($key, $arrA)) {
                if (!$preserveNumericKeys && is_int($key)) {
                    $arrA[] = $value;
                } elseif (is_array($value) && is_array($arrA[$key])) {
                    $arrA[$key] = static::merge($arrA[$key], $value, $preserveNumericKeys);
                } else {
                    $arrA[$key] = $value;
                }
            } else {
                $arrA[$key] = $value;
            }
        }
        return $arrA;
    }
}
