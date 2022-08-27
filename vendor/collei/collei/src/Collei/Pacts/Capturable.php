<?php
namespace Collei\Pacts;

/**
 *	allow classes to be creatable with environment info gathering
 *
 *	@author	alarido
 *	@since	2022-04-23
 */
interface Capturable
{
	/**
	 *	Creates a new instance of the class by capturing some info
	 *	from the environment or elsewhere.
	 *
	 *	@return	mixed
	 */
	public static function capture();
}


