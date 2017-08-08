<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

function str_slugify($string, $space = '-', $ascii = true, $lower = true)
{
    if ($ascii) {
        $string = iconv('utf-8', 'ascii//translit//ignore', $string);
    }
    if ($lower) {
        $string = mb_strtolower($string);
    }
    $string = \preg_replace('/(^[^a-z0-9]+|[^a-z0-9]+$)/ui', '', $string);
    $string = \preg_replace('/\'+/ui', '', $string);
    $string = \preg_replace('/[^a-z0-9]+/ui', $space, $string);
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
    return \str_replace(' ', null, \ucwords(\Coast\str_slugify($string, ' ', false, false)));
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

function str_trim_smart($string, $limit, $overflow = null)
{
    $pos = strlen($string) > $limit
        ? strpos($string, ' ', $limit)
        : false;
    if ($pos !== false) {
        $string = \substr($string, 0, $pos);
        if (isset($overflow)) {
            $string = \rtrim($string, ' .,!?') . $overflow;
        }
    }
    return $string;
}

function str_trim_words($string, $limit, $overflow = null)
{
    $string = strip_tags($string);
    if (str_word_count($string, 0) > $limit) {
        $words = str_word_count($string, 2);
        $pos   = array_keys($words);
        $string = mb_substr($string, 0, $pos[$limit] - 1, 'utf8') . $overflow;
    }
    return $string;
}

function str_to_bytes($string)
{
    if (is_numeric($string)) {
       return $string;
    }
    $string = trim($string);  
    $size   = substr($string, 0, -1);  
    $suffix = substr($string, -1);  
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