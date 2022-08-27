<?php
namespace Collei\Database;

/**
 *	Objects from which something may be gathered
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
interface Gatherable
{
	/**
	 *	Returns something
	 *
	 *	@param	bool	$asObject
	 *	@return	mixed
	 */
	public function gather(bool $asObject = false);
}

