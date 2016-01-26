<?php
namespace Morpho\Base;

class ArrayTool {
    public static function cartesianProduct(...$arrs) {
        // @TODO
        throw new NotImplementedException();
    }

    public static function permutations(array $arr, int $n, bool $allowDups = false): array {
        throw new NotImplementedException();
    }

    public static function combinations(array $arr, int $n, bool $allowDups = false): array {
        throw new NotImplementedException();
    }

    public static function getItemsWithKeys(array $arr, array $keys): array {
        return array_intersect_key($arr, array_flip(array_values($keys)));
    }

    /**
     * Union for sets, for difference use array_diff(), for intersection use array_intersect().
     */
    public static function union(...$arr): array {
        return array_unique(array_merge(...$arr));
    }

    /**
     * Symmetrical difference of the two sets: ($a \ $b) ∪ ($b \ $a)
     */
    public static function symmetricDiff(array $a, array $b): array {
        $diffA = array_diff_assoc($a, $b);
        $diffB = array_diff_assoc($b, $a);
        foreach ($diffB as $key => $value) {
            $diffA[$key] = $value;
        }
        return $diffA;
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
            throw new \UnexpectedValueException("Empty list");
        }
        return array_shift($list);
    }

    public static function tail(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        array_shift($list);
        return $list;
    }

    public static function last(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        return array_pop($list);
    }

    public static function init(array $list) {
        // @TODO: Move to the \Base
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        return array_slice($list, 0, -1, true);
    }

    /**
     * @param array $matrix
     * @param string $key
     * @param bool $drop
     * @return array
     * @throws \RuntimeException
     */
    public static function toKeyed(array $matrix, $keyForIndex, bool $drop = false): array {
        $result = [];
        foreach ($matrix as $row) {
            if (!isset($row[$keyForIndex])) {
                throw new \RuntimeException();
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
        Assert::hasOnlyKeys($options, array_keys($defaultOptions));
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

    public static function getHash(array $arr): string {
        return md5(json_encode($arr));
    }
}
