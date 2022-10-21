<?php
namespace Collei\Support;

use InvalidArgumentException;

/**
 *	Number helpers
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class Number
{
	/**
	 *	Constrains number to values between $min and $max
	 *
	 *	@param	int|float|string	$number
	 *	@param	int|float|string	$min
	 *	@param	int|float|string	$max
	 *	@return	int|float
	 *	@throws \InvalidArgumentException
	 */
	public static function constrict($number, $min, $max)
	{
		if (is_int($number) && is_numeric($min) && is_numeric($max))
		{
			$number = (int)$number;
			$min = (int)$min;
			$max = (int)$max;
		}
		elseif (is_double($number) && is_numeric($min) && is_numeric($max))
		{
			$number = (float)$number;
			$min = (float)$min;
			$max = (float)$max;
		}
		else
		{
			throw new InvalidArgumentException("Arguments must be either int or float !");
		}

		if ($number <= $min)
		{
			return $min;
		}
		if ($number >= $max)
		{
			return $max;
		}
		return $number;
	}

}


