<?php

namespace AppBundle\Common;

class ArrayToolkit
{
    public static function get(array $array, $key, $default)
    {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    public static function column(array $array, $columnName)
    {
        if (function_exists('array_column')) {
            return array_column($array, $columnName);
        }

        if (empty($array)) {
            return array();
        }

        $column = array();

        foreach ($array as $item) {
            if (isset($item[$columnName])) {
                $column[] = $item[$columnName];
            }
        }

        return $column;
    }

    public static function parts(array $array, array $keys)
    {
        foreach (array_keys($array) as $key) {
            if (!in_array($key, $keys)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function requires(array $array, array $keys, $strictMode = false)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
            if ($strictMode && (is_null($array[$key]) || $array[$key] === '' || $array[$key] === 0)) {
                return false;
            }
        }

        return true;
    }

    public static function group(array $array, $key)
    {
        $grouped = array();

        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }

            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    public static function index(array $array, $name)
    {
        $indexedArray = array();

        if (empty($array)) {
            return $indexedArray;
        }

        foreach ($array as $item) {
            if (isset($item[$name])) {
                $indexedArray[$item[$name]] = $item;
                continue;
            }
        }

        return $indexedArray;
    }

    public static function rename(array $array, array $map)
    {
        $keys = array_keys($map);

        foreach ($array as $key => $value) {
            if (in_array($key, $keys)) {
                $array[$map[$key]] = $value;
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function filter(array $array, array $specialValues)
    {
        $filtered = array();

        foreach ($specialValues as $key => $value) {
            if (!array_key_exists($key, $array)) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = (array) $array[$key];
            } elseif (is_int($value)) {
                $filtered[$key] = (int) $array[$key];
            } elseif (is_float($value)) {
                $filtered[$key] = (float) $array[$key];
            } elseif (is_bool($value)) {
                $filtered[$key] = (bool) $array[$key];
            } else {
                $filtered[$key] = (string) $array[$key];
            }

            if (empty($filtered[$key])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public static function trim($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = static::trim($value);
            } elseif (is_string($value)) {
                $array[$key] = trim($value);
            }
        }

        return $array;
    }

    public static function every($array, $callback = null)
    {
        foreach ($array as $value) {
            if ((is_null($callback) && !$value) || (is_callable($callback) && !$callback($value))) {
                return false;
            }
        }

        return true;
    }

    public static function some($array, $callback = null)
    {
        foreach ($array as $value) {
            if ((is_null($callback) && $value) || (is_callable($callback) && $callback($value))) {
                return true;
            }
        }

        return false;
    }

    public static function flatToTree($flatArrays, $parentId, $autoFillIdKeyAndParentIdKey = true, $keyMap = array())
    {
        $parentIdKey = isset($keyMap['parentIdKey']) ? $keyMap['parentIdKey'] : 'parent_id';
        $idKey = isset($keyMap['$idKey']) ? $keyMap['$idKey'] : 'id';
        $childrenKey = isset($keyMap['childrenKey']) ? $keyMap['childrenKey'] : 'children';

        $treeBranch = array();

        foreach ($flatArrays as $key => &$item) {
            if ($item[$parentIdKey] == $parentId) {
                $itemId = isset($item[$idKey]) ? $item[$idKey] : $key;
                $children = self::flatToTree($flatArrays, $itemId, $keyMap);
                if ($children) {
                    foreach ($children as $childKey => &$child) {
                        if (!isset($child[$idKey]) && $autoFillIdKeyAndParentIdKey) {
                            $child[$idKey] = $childKey;
                        }
                    }
                    $item[$childrenKey] = $children;
                }
                if (!isset($item[$idKey])) {
                    if ($autoFillIdKeyAndParentIdKey) {
                        $item[$idKey] = $key;
                    }
                    $treeBranch[$key] = $item;
                } else {
                    $treeBranch[] = $item;
                }
            }
        }

        return $treeBranch;
    }

    public static function treeToFlat($tree, $autoFillIdKeyAndParentIdKey = true, $keyMap = array())
    {
        $parentIdKey = isset($keyMap['parentIdKey']) ? $keyMap['parentIdKey'] : 'parent_id';
        $idKey = isset($keyMap['$idKey']) ? $keyMap['$idKey'] : 'id';
        $childrenKey = isset($keyMap['childrenKey']) ? $keyMap['childrenKey'] : 'children';

        $faltArrays = array();
        foreach ($tree as $key => &$node) {
            if (!isset($node[$idKey])) {
                $faltArrays[$key] = &$node;
                if ($autoFillIdKeyAndParentIdKey) {
                    $node[$idKey] = $key;
                }
            } else {
                $faltArrays[] = &$node;
            }

            if (!isset($node[$parentIdKey])) {
                $node[$parentIdKey] = '';
            }

            if (!empty($node[$childrenKey])) {
                $children = self::treeToFlat($node[$childrenKey], $keyMap);
                foreach ($children as $childKey => &$child) {
                    if (empty($child[$parentIdKey])) {
                        $child[$parentIdKey] = $key;
                    }
                    if (!isset($child[$idKey]) && $autoFillIdKeyAndParentIdKey) {
                        $child[$idKey] = $childKey;
                    }
                }

                $faltArrays = array_merge($faltArrays, $children);

                unset($node[$childrenKey]);
            }
        }

        return $faltArrays;
    }
}
