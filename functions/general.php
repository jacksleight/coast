<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Url;
use ReflectionProperty;
use ReflectionMethod;

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
        $_extName = pathinfo($_real, PATHINFO_EXTENSION);
        if (in_array($_extName, ['json', 'webmanifest'])) {
            $_coastLoad[$_real] = json_decode(file_get_contents($_real));
        } else {
            extract($_vars);
            $_coastLoad[$_real] = require $_real;
        }
    }
    return $_coastLoad[$_real];
}

function css($properties)
{
    $lines = array();
    foreach ($properties as $name => $value) {
        if ($value instanceof Url) {
            $value = "url('{$value}')";
        }
        $lines[] = "{$name}: {$value};";
    }
    return implode(' ', $lines);
}

function css_ratio($width, $height)
{
    return \Coast\css(array(
        'padding-top' => (($height / $width) * 100) . "%",
    ));
}

function css_ratio_slope($smallWidth, $smallHeight, $largeWidth, $largeHeight)
{
    $slope = ($largeHeight - $smallHeight) / ($largeWidth - $smallWidth);
    return \Coast\css(array(
        'padding-top' => ($slope * 100) . "%",
        'height'      => ($smallHeight - $smallWidth * $slope) . 'px'
    ));
}

function pseudo_random($bytes = 32, $algo = 'sha512')
{
    return base64_encode(hash($algo, openssl_random_pseudo_bytes($bytes), true));
}

function base64_urlsafe($string)
{
    return rtrim(str_replace(['+', '/'], ['-', '_'], $string), '=');
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

function property_is_public($object, $property) {
    return (new ReflectionProperty($object, $property))->isPublic();
}

function method_is_public($object, $method) {
    return (new ReflectionMethod($object, $method))->isPublic();
}