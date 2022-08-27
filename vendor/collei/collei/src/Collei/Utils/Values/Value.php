<?php
namespace Collei\Utils\Values;

use Collei\Utils\Calendar\Date;

/**
 *	Embodies tasks on value
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-17
 */
class Value
{
	/**
	 *	Constants
	 */
	public const TYPE_UNDEFINED = -1;
	public const TYPE_UNTYPED = 0;
	public const TYPE_BOOL = 1;
	public const TYPE_INT = 2;
	public const TYPE_DATE = 3;
	public const TYPE_DOUBLE = 4;
	public const TYPE_STRING = 5;
	public const TYPE_BLOB = 10;
	public const TYPE_OBJECT = 11;

	public const VALUE_MAX_INT = 2147483647;
	public const VALUE_MAX_BIGINT = 9223372036854775807;

	private const TYPE_STRINGS_DESC = [
		self::TYPE_UNTYPED => 'untyped',
		self::TYPE_BOOL => 'bool',
		self::TYPE_INT => 'int',
		self::TYPE_DATE => 'date',
		self::TYPE_DOUBLE => 'double',
		self::TYPE_STRING => 'string',
		self::TYPE_BLOB => 'blob',
		self::TYPE_OBJECT => 'object',
	];

	/**
	 *	Returns a string short description of the type
	 *
	 *	@param	int	$type
	 *	@return	string
	 */
	public static function toString(int $type)
	{
		return self::TYPE_STRINGS_DESC[$type] ?? 'undefined';
	}

	/**
	 *	Returns type integer code from type names
	 *
	 *	@static
	 *	@param	string	$type
	 *	@return	int
	 */
	private static function typeFromString(string $type)
	{
		$type_bank = [
			Value::TYPE_BOOL => ['bool','boolean','bit'],
			Value::TYPE_INT => ['byte','word','int','integer','long','longint'],
			Value::TYPE_DATE => ['date','datetime','smalldate','smalldatetime'],
			Value::TYPE_DOUBLE => ['float','double','currency','decimal','real'],
			Value::TYPE_STRING => ['string','char','varchar','text','enum'],
			Value::TYPE_BLOB => ['blob','binary','varbinary']
		];
		$type_in = strtolower(trim($type));

		foreach ($type_bank as $type_id => $types)
		{
			if (in_array($type_in, $types))
			{
				return $type_id;
			}
		}
		return Value::TYPE_UNDEFINED;
	}

	/**
	 *	Performs analysis on the data to determine the most fitting type
	 *
	 *	@static
	 *	@param	mixed	$value
	 *	@return	int
	 */
	public static function prospectType($value)
	{
		if (is_null($value))
		{
			return Value::TYPE_UNDEFINED;
		}
		if (!is_null(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)))
		{
			return Value::TYPE_BOOL;
		}
		if (is_numeric($value))
		{
			$n = 0 + $value;
			if (is_int($n))
			{
				return Value::TYPE_INT;
			}
			else
			{
				return Value::TYPE_DOUBLE;
			}
		}
		if (Date::isDate($value))
		{
			return Value::TYPE_DATE;
		}
		if (is_object($value))
		{
			return Value::TYPE_OBJECT;
		}
		return Value::TYPE_STRING;
	}

	/**
	 *	Performs value cast to the most fitting type
	 *
	 *	@static
	 *	@param	mixed	$value
	 *	@return	mixed
	 */
	public static function cast($value)
	{
		$prospectedType = Value::prospectType($value);
		//
		if ($prospectedType == Value::TYPE_BOOL)
			return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if ($prospectedType == Value::TYPE_INT)
			return (int)$value;
		if ($prospectedType == Value::TYPE_DOUBLE)
			return (double)$value;
		if ($prospectedType == Value::TYPE_DATE)
			return new Date($value);
		if ($prospectedType == Value::TYPE_OBJECT)
			return (object)$value;
		//
		return (string)$value;
	}

	/**
	 *	Performs value cast to the specified type (if possible) or bust
	 *
	 *	@static
	 *	@param	mixed	$value
	 *	@param	string	$targetType
	 *	@param	mixed	$else
	 *	@return	mixed
	 */
	public static function castTo($value, string $targetType, $else = null)
	{
		$prospectedType = Value::prospectType($value);
		//
		if (in_array($targetType, ['bool','boolean']))
		{
			if ($prospectedType == Value::TYPE_BOOL)
				return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
			//
			return is_bool($else) ? $else : false;
		}
		//
		if (in_array($targetType, ['int','integer']))
		{
			if ($prospectedType == Value::TYPE_BOOL)
				return ((filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false) ? 1 : 0);
			if ($prospectedType == Value::TYPE_INT)
				return (int)$value;
			if ($prospectedType == Value::TYPE_DOUBLE)
				return (int)(double)$value;
			//
			return is_numeric($value) ? (int)(double)$value : $else;
		}
		//
		if (in_array($targetType, ['double','float','real']))
		{
			if ($prospectedType == Value::TYPE_BOOL)
				return (double)((filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false) ? 1 : 0);
			if ($prospectedType == Value::TYPE_INT)
				return (double)(int)$value;
			if ($prospectedType == Value::TYPE_DOUBLE)
				return (double)$value;
			//
			return is_numeric($value) ? (double)$value : $else;
		}
		//
		if (in_array($targetType, ['array']))
		{
			return [$value];
		}
		//
		if (in_array($targetType, ['string']))
		{
			return (string)$value;
		}
		//
		return $else;
	}

}


