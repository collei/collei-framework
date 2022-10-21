<?php
namespace Collei\Contracts;

/**
 *	allow classes to be serialized into a JSON format string
 *
 *	@author	alarido
 *	@since	2022-03-27
 */
interface Jsonable
{
	/**
	 *	converts the object data to Json string
	 *
	 *	@return	string
	 */
	public function toJson();
}


