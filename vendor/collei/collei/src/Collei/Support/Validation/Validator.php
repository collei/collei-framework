<?php
namespace Collei\Support\Validation;

/**
 *	Validation tasks
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-14
 */
class Validator
{
	/**
	 *	Performs inclusive range validation of a numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$min
	 *	@param	float	$max
	 *	@return	bool
	 */
	public static function inRange(float $value, float $min, float $max)
	{
		return ($value >= $min) && ($value <= $max);
	}

	/**
	 *	Performs exclusive range validation of a numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$min
	 *	@param	float	$max
	 *	@return	bool
	 */
	public static function inRangeExclusive(float $value, float $min, float $max)
	{
		return ($value > $min) && ($value < $max);
	}

	/**
	 *	Performs validation of exclusively maximum numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$max
	 *	@return	bool
	 */
	public static function isBelow(float $value, float $max)
	{
		return ($value < $max);
	}

	/**
	 *	Performs validation of inclusively maximum numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$max
	 *	@return	bool
	 */
	public static function isBelowOrThere(float $value, float $max)
	{
		return ($value <= $max);
	}

	/**
	 *	Performs validation of exclusively minimum numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$min
	 *	@return	bool
	 */
	public static function isAbove(float $value, float $min)
	{
		return ($value > $min);
	}

	/**
	 *	Performs validation of inclusively minimum numeric value
	 *
	 *	@static
	 *	@param	float	$value
	 *	@param	float	$min
	 *	@return	bool
	 */
	public static function isAboveOrThere(float $value, float $min)
	{
		return ($value > $min);
	}

}


