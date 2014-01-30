<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

function is_array_assoc(array $array)
{
    if (is_array($array)) {
        \krsort($array, SORT_STRING);
        return !\is_numeric(key($array));
    }
    return false;
}

function array_pairs(array $array, $key, $value)
{
    $pairs = [];
    foreach ($array as $item) {
        $pairs[$item[$key]] = $item[$value];
    }
    return $pairs;
}

function array_column(array $array, $key)
{
    $values = [];
    foreach ($array as $item) {
        $values[] = $item[$key];
    }
    return $values;
}

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

function array_avg(array $array)
{
    if (!\count($array)) {
        return 0;
    }
    return \array_sum($array) / \count($array);
}

function math_gcd($a, $b)
{
    while ($b !== 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return $a;
}

function math_ratio($a, $b)
{
    $gcd = \Coast\math_gcd($a, $b);
    return [$a / $gcd, $b / $gcd];
}

function str_simplify($string, $spacer = '', $lowercase = true)
{
    if ($lowercase) {
        $string = \strtolower($string);
    }
    $string = \preg_replace('/(^[^a-z0-9]+|[^a-z0-9]+$)/i', '', $string);
    $string = \preg_replace('/\'+/i', '', $string);
    $string = \preg_replace('/[^a-z0-9]+/i', $spacer, $string);
    return $string;
}

function str_camel($string)
{
    return \Coast\str_camel_lower($string);
}

function str_camel_lower($string)
{
    if (strlen($string)) {
        $string = \Coast\str_camel_upper($string);
        $string = \strtolower($string[0]) . \substr($string, 1);
    }
    return $string;
}

function str_camel_upper($string)
{
    return \str_replace(' ', null, \ucwords(\Coast\str_simplify($string, ' ')));
}

function str_camel_split($string, $spacer = ' ')
{
    $string = \preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1'.$spacer.'$2', $string);
    $string = \preg_replace('/([a-zd])([A-Z])/', '$1'.$spacer.'$2', $string);
    return $string;
}

function str_size_format($size, $decimals = 2, $spacer = ' ')
{
    if (!$size) {
        return null;
    }
    $names = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $floor = \floor(log($size, 1024));
    $round = \round($size / \pow(1024, $floor), $decimals);
    return $round . $spacer . $names[$floor];
}

function str_uchr($code)
{
    return \html_entity_decode('&'. (\is_numeric($code) ? '#' . $code : $code) .';', ENT_NOQUOTES, 'utf-8');
}

function str_to_bytes($value)
{
    $value = \trim($value);
    $last = \strtolower($value[\strlen($value)-1]);
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    return $value;
}