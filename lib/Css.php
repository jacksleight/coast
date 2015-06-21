<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

class Css
{
	public function __invoke($properties)
	{
		return $this->properties($properties);
	}

	public function properties($properties)
	{
		$lines = array();
		foreach ($properties as $name => $value) {
			$lines[] = "{$name}: {$value};";
		}
		return implode(' ', $lines);
	}
	
	public function ratio($width, $height)
	{
		return $this->properties(array(
			'padding-top' => (($height / $width) * 100) . "%",
		));
	}

	public function ratioSlope($smallWidth, $smallHeight, $largeWidth, $largeHeight)
	{
		$slope = ($largeHeight - $smallHeight) / ($largeWidth - $smallWidth);
		return $this->properties(array(
			'padding-top' => ($slope * 100) . "%",
			'height'      => ($smallHeight - $smallWidth * $slope) . 'px'
		));
	}
}