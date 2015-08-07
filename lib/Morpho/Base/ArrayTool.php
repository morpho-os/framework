<?php
namespace Morpho\Base;

class ArrayTool {
    public static function filterByKeys(array $arr, array $keys) {
        return array_intersect_key($arr, array_flip(array_values($keys)));
    }

    /**
     * Symmetrical difference.
     *
     * @param array $a
     * @param array $b
     * @return array
     */
    public static function symmetricDiff(array $a, array $b) {
        $diffA = array_diff_assoc($a, $b);
        $diffB = array_diff_assoc($b, $a);
        foreach ($diffB as $key => $value) {
            $diffA[$key] = $value;
        }
        return $diffA;
    }

    public static function flatten(array $arr) {
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
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        return array_shift($list);
    }

    public static function tail(array $list) {
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        array_shift($list);
        return $list;
    }

    public static function last(array $list) {
        if (!count($list)) {
            throw new \UnexpectedValueException("Empty list");
        }
        return array_pop($list);
    }

    public static function init(array $list) {
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
    public static function toKeyed(array $matrix, $key, $drop = false) {
        $result = [];
        foreach ($matrix as $row) {
            if (!isset($row[$key])) {
                throw new \RuntimeException();
            }
            $k = $row[$key];
            if ($drop) {
                unset($row[$key]);
            }
            $result[$k] = $row;
        }
        return $result;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function camelizeKeys(array $array) {
        $result = [];
        foreach ($array as $key => $value) {
            $result[camelize($key)] = $value;
        }

        return $result;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function underscoreKeys(array $array) {
        $result = [];
        foreach ($array as $key => $value) {
            $result[underscore($key)] = $value;
        }

        return $result;
    }

    /**
     * @param array $options
     * @param array $defaultOptions
     * @return array
     */
    public static function handleOptions(array $options, array $defaultOptions) {
        if (count($options) > 0) {
            self::ensureHasOnlyKeys($options, array_keys($defaultOptions));
            return array_merge($defaultOptions, $options);
        }
        return $defaultOptions;
    }

    /**
     * @param array $actual
     * @param array $requiredKeys
     * @param array $allowedKeys
     */
    public static function checkItems(array $actual, array $requiredKeys, array $allowedKeys) {
        self::checkRequiredItems($actual, $requiredKeys);
        // The $requiredKeys is always subset of the $allowedKeys, allow don't enumerate the same item keys twice.
        self::ensureHasOnlyKeys($actual, array_unique(array_merge($requiredKeys, $allowedKeys)));
    }

    /**
     * @param array $actual
     * @param array $allowedKeys
     * @throws \RuntimeException
     */
    public static function ensureHasOnlyKeys(array $actual, array $allowedKeys) {
        $diff = array_diff_key($actual, array_flip($allowedKeys));
        if (count($diff)) {
            throw new \RuntimeException("Not allowed items are present.");
        }
    }

    /**
     * @param array $actual
     * @param array $requiredKeys
     * @throws \RuntimeException
     */
    public static function checkRequiredItems(array $actual, array $requiredKeys) {
        $intersection = array_intersect_key(array_flip($requiredKeys), $actual);
        if (count($intersection) != count($requiredKeys)) {
            throw new \RuntimeException("Required items are missing.");
        }
    }

    /**
     * Unsets all items of array with $key recursively.
     */
    public static function unsetRecursive(array &$arr, $key) {
        unset($arr[$key]);
        foreach (array_keys($arr) as $k) {
            if (is_array($arr[$k])) {
                self::unsetRecursive($arr[$k], $key);
            }
        }

        return $arr;
    }

    /**
     * @return string A hash of array.
     */
    public static function getHash(array $array) {
        return md5(json_encode($array));
    }
}
