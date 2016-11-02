<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

define(__NAMESPACE__ . '\DATETIME', 'Y-m-d H:i:s');
define(__NAMESPACE__ . '\DATE', 'Y-m-d');
define(__NAMESPACE__ . '\TIME', 'H:i:s');

/**
 * Load a file without leaking variables, cache and reuse return value.
 * @param  mixed   $_file
 * @param  array   $_vars
 * @return mixed
 */
function load($_file, array $_vars = array())
{
    global $_coastLoad;
    if (!isset($_coastLoad)) {
        $_coastLoad = [];
    }
    $_real = realpath($_file);
    if (!$_real) {
        throw new \Exception("File '{$_file}' could not be found");
    }
    if (!array_key_exists($_real, $_coastLoad)) {
        extract($_vars);
        $_coastLoad[$_real] = require $_real;
    }
    return $_coastLoad[$_real];
}

/**
 * Is array key/value pairs.
 * @param  array   $array
 * @return boolean
 */
function is_array_assoc(array $array)
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

/**
 * Calculate the greatest common denominator of two values.
 * @param  float $a
 * @param  float $b
 * @return float
 */
function gcd($a, $b)
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
    $gcd = \Coast\gcd($a, $b);
    return [$a / $gcd, $b / $gcd];
}

function str_slugify($string, $space = '-')
{
    $string = \preg_replace('/(^[^a-z0-9]+|[^a-z0-9]+$)/i', '', $string);
    $string = \preg_replace('/\'+/i', '', $string);
    $string = \preg_replace('/[^a-z0-9]+/i', $space, $string);
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
    return \str_replace(' ', null, \ucwords(\Coast\str_slugify($string, ' ')));
}

function str_camel_split($string, $space = ' ')
{
    $string = \preg_replace(array('/(?<=[^A-Z])([A-Z])/', '/(?<=[^0-9])([0-9])/'), $space . '$0', $string);
    return $string;
}

function str_size_format($size, $decimals = 2, $space = ' ')
{
    if (!$size) {
        return null;
    }
    $names = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $floor = \floor(log($size, 1024));
    $round = \round($size / \pow(1024, $floor), $decimals);
    return $round . $space . $names[$floor];
}

function str_uchr($code)
{
    return \html_entity_decode('&'. (\is_numeric($code) ? '#' . $code : $code) .';', ENT_NOQUOTES, 'utf-8');
}

function str_trim_smart($value, $limit, $overflow = null)
{
    $pos = strlen($value) > $limit
        ? strpos($value, ' ', $limit)
        : false;
    if ($pos !== false) {
        $value = \substr($value, 0, $pos);
        if (isset($overflow)) {
            $value = \rtrim($value, ' .,!?') . $overflow;
        }
    }
    return $value;
}

function str_trim_words($value, $limit, $overflow = null)
{
    $value = strip_tags($value);
    if (str_word_count($value, 0) > $limit) {
        $words = str_word_count($value, 2);
        $pos   = array_keys($words);
        $value = mb_substr($value, 0, $pos[$limit] - 1, 'utf8') . $overflow;
    }
    return $value;
}

function str_to_bytes($value)
{
    if (is_numeric($value)) {
       return $value;
    }
    $value  = trim($value);  
    $size   = substr($value, 0, -1);  
    $suffix = substr($value, -1);  
    switch(strtoupper($suffix)) {
        case 'P':
            $size *= 1024;
        case 'T':
            $size *= 1024;
        case 'G':
            $size *= 1024;
        case 'M':
            $size *= 1024;
        case 'K':
            $size *= 1024;
            break;
    }
    return $size;
}

function str_css($properties)
{
    $lines = array();
    foreach ($properties as $name => $value) {
        $lines[] = "{$name}: {$value};";
    }
    return implode(' ', $lines);
}

function str_css_ratio($width, $height)
{
    return \Coast\str_css(array(
        'padding-top' => (($height / $width) * 100) . "%",
    ));
}

function str_css_ratio_slope($smallWidth, $smallHeight, $largeWidth, $largeHeight)
{
    $slope = ($largeHeight - $smallHeight) / ($largeWidth - $smallWidth);
    return \Coast\str_css(array(
        'padding-top' => ($slope * 100) . "%",
        'height'      => ($smallHeight - $smallWidth * $slope) . 'px'
    ));
}

function str_random($bytes = 32, $algo = 'sha512')
{
    return rtrim(base64_encode(hash($algo, openssl_random_pseudo_bytes($bytes), true)), '=');
}

function number_ordinal($number, $exclude = false) {
    $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    $ordinal = (($number % 100) >= 11) && (($number % 100) <= 13)
        ? 'th'
        : $ends[$number % 10];
    return $exclude
        ? $ordinal
        : $number . $ordinal;
}