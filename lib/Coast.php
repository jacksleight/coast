<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

function array_is_assoc(array $array)
{
	if (is_array($array)) {
		\krsort($array, SORT_STRING);
		return !\is_numeric(key($array));
	}
	return false;
}

function array_pairs(array $array, $key, $value)
{
	$pairs = array();
	foreach ($array as $item) {
		$pairs[$item[$key]] = $item[$value];
	}
	return $pairs;
}

function array_column(array $array, $key)
{
	$values = array();
	foreach ($array as $item) {
		$values[] = $item[$key];
	}
	return $values;
}

function array_merge_smart()
{
	$merged = array();
	$arrays = \func_get_args();
	foreach ($arrays as $array) {
		foreach ($array as $key => $value) {
			if (\is_array($value) && isset($merged[$key])) {
				if (!\Coast\array_is_assoc($merged[$key]) && !\Coast\array_is_assoc($value)) {
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
	$output = array();
	foreach ($array as $key => $value) {
		if (isset($value)) {
			$output[$key] = $value;
		}
	}
	return $output;
}

function array_filter_null_recursive($array)
{
	$output = array();
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

function array_contract(array $array, $delimeter)
{
	$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array), \RecursiveIteratorIterator::SELF_FIRST);
	$output = array();
	$keys = array();
	foreach ($iterator as $key => $value)	
	{
		\array_splice($keys, $iterator->getDepth(), \count($keys), array($key));
		if (!\is_array($value)) {
			$output[\implode($delimeter, $keys)] = $value;
		}
	}
	return $output;
}

function array_expand(array $array, $delimiter)
{
	$output = array();
	foreach ($array as $name => $value) {
		$pointer =& $output;
		$name = \explode($delimiter, $name);
		foreach ($name as $part) {
			if (!isset($pointer[$part])) {
				$pointer[$part] = array();
			}
			$pointer =& $pointer[$part];
		}
		$pointer = $value;
		unset($pointer);
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

function array_average(array $array)
{
	if (!\count($array)) {
		return 0;
	}
	return \array_sum($array) / \count($array);
}

function array_implode_pairs($glue, $colon, $array)
{
	$parts = array();
	foreach ($array as $key => $value) {
		$parts[] = $key . $colon . $value;
	}
	return \implode($glue, $parts);
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
	return array($a / $gcd, $b / $gcd);
}

function object_vars($object)
{
	return \get_object_vars($object);
}

function openssl_keypair()
{
	$resource = \openssl_pkey_new();
	if (!$resource) {
		return false;
	}
	\openssl_pkey_export($resource, $private);
	$public = \openssl_pkey_get_details($resource);
	$public = $public['key'];
	return array(
		'public'	=> $public,
		'private'	=> $private,
	);
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
	$names = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$floor = \floor(log($size, 1024));
	$round = \round($size / \pow(1024, $floor), $decimals);
	return $round . $spacer . $names[$floor];
}

function str_password($length)
{
	$characters = array(
		'abcdefghijklmnopqrstuvwxyz',
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'0123456789',
	);
	$password = '';
	$j = 0;
	for ($i = 0; $i < $length; $i++) {
		$password .= \substr($characters[$j], \rand(0, \strlen($characters[$j]) - 1), 1);
		$j = ($j == 2) ? 0 : $j + 1;
	}
	return $password;
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

function doctrine_configure(\Doctrine\ORM\Configuration $configuration)
{
	\Doctrine\DBAL\Types\Type::addType('json', 'Coast\Doctrine\Dbal\Types\JSONType');
	\Doctrine\DBAL\Types\Type::addType('url', 'Coast\Doctrine\Dbal\Types\URLType');

	$configuration->addCustomStringFunction('CEILING', 'Coast\Doctrine\Orm\Query\Mysql\Ceiling');
	$configuration->addCustomStringFunction('FIELD', 'Coast\Doctrine\Orm\Query\Mysql\Field');
	$configuration->addCustomStringFunction('FLOOR', 'Coast\Doctrine\Orm\Query\Mysql\Floor');
	$configuration->addCustomStringFunction('IF', 'Coast\Doctrine\Orm\Query\Mysql\IfElse');
	$configuration->addCustomStringFunction('ROUND', 'Coast\Doctrine\Orm\Query\Mysql\Round');
	$configuration->addCustomStringFunction('UTC_DATE', 'Coast\Doctrine\Orm\Query\Mysql\UtcDate');
	$configuration->addCustomStringFunction('UTC_TIME', 'Coast\Doctrine\Orm\Query\Mysql\UtcTime');
	$configuration->addCustomStringFunction('UTC_TIMESTAMP', 'Coast\Doctrine\Orm\Query\Mysql\UtcTimestamp');
}