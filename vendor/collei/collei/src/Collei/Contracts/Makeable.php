<?php
namespace Collei\Contracts;

/**
 *	allow classes to be easily creatable.
 *
 *	@author	alarido
 *	@since	2022-04-23
 */
interface Makeable
{
	/**
	 *	Creates a new instance of the class. 
	 *
	 *	@static
	 *	@return	mixed
	 */
	public static function make();
}


