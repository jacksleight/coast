<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

/**
 * Calculate the greatest common denominator of two values.
 * @param  float $a
 * @param  float $b
 * @return float
 */
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
