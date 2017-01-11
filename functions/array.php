<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

/**
 * Is array key/value pairs.
 * @param  array   $array
 * @return boolean
 */
function is_array_assoc($array)
{
    if (is_array($array)) {
        \krsort($array, SORT_STRING);
        return !\is_numeric(key($array));
    }
    return false;
}

/**
 * Extract two params of a multidimensional array to a new key/value pair array.
 * @param  array  $array
 * @param  string $key
 * @param  string $value
 * @return array
 */
function array_pairs(array $array, $key, $value)
{
    $pairs = [];
    foreach ($array as $item) {
        $pairs[$item[$key]] = $item[$value];
    }
    return $pairs;
}

/**
 * Extract one param of a multidimensional array to a new array.
 * @param  array  $array
 * @param  string $key
 * @return array
 */
function array_column(array $array, $key)
{
    $values = [];
    foreach ($array as $item) {
        $values[] = $item[$key];
    }
    return $values;
}

/**
 * Recursively merge arrays, overwriting keys when arrays are key/value pairs, merging when numeric.
 * @return array
 */
function array_merge_smart()
{
    $merged = [];
    $arrays = \func_get_args();
    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            if (\is_array($value) && isset($merged[$key])) {
                if (!\Coast\is_array_assoc($merged[$key]) && !\Coast\is_array_assoc($value)) {
                    $merged[$key] = \array_merge($merged[$key], $value);
                } else {
                    $merged[$key] = \Coast\array_merge_smart($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }                
        }
    }
    return $merged;
}

/**
 * Remove null values from array.
 * @param  array $array
 * @return array
 */
function array_filter_null($array)
{
    $output = [];
    foreach ($array as $key => $value) {
        if (isset($value)) {
            $output[$key] = $value;
        }
    }
    return $output;
}

/**
 * Recursively remove null values from array.
 * @param  array $array
 * @return array
 */
function array_filter_null_recursive($array)
{
    $output = [];
    foreach ($array as $key => $value) {
        if (\is_array($value)) {
            $value = \Coast\array_filter_null_recursive($value);
            if (\count($value) > 0) {
                $output[$key] = $value;
            }
        } elseif (isset($value)) {
            $output[$key] = $value;
        }
    }
    return $output;
}

function array_intersect_key(array $array1, array $array2)
{
    return \array_intersect_key($array1, \array_fill_keys($array2, null));
}

function array_diff_key(array $array1, array $array2)
{
    return \array_diff_key($array1, \array_fill_keys($array2, null));
}

/**
 * Calculate the mean of all values in an array
 * @param  array  $array
 * @return float
 */
function array_mean(array $array)
{
    if (!\count($array)) {
        return 0;
    }
    return \array_sum($array) / \count($array);
}

function array_object_smart(array $array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = array_object_smart($value);
        }
    }
    return \Coast\is_array_assoc($array)
        ? (object) $array
        : $array;
}