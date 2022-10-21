<?php
namespace Collei\Support\Validation;

/**
 *	Embodies value checker tasks
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-14
 */
class ValueChecker
{
	/**
	 *	Checks if it is possible to cast the given $obj as string
	 *
	 *	@static
	 *	@param	object	$obj
	 *	@return	bool
	 */
	public static function objectIsStringable(object $obj)
	{
		if (function_exists('method_exists'))
		{
			return method_exists($obj, '__toString');
		}
		//
		try
		{
			$str = (string)$obj;
		}
		catch (Exception $e)
		{
			return false;
		}
		catch (Throwable $e)
		{
			return false;
		}
		return true;
	}

}


