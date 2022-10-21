<?php
namespace Collei\Support;

/**
 *	Some server helpers
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class Server
{
	/**
	 *	Maps the given path to the server's OS filesystem
	 *
	 *	@param	string	$virtual
	 *	@return	string|null
	 */
	public static function mapRoot(string $virtual)
	{
		$bt = debug_backtrace();

		logit('called stancer', print_r($bt, true));
	}

	/**
	 *	Maps the path of the caller's script file to the server's OS filesystem
	 *
	 *	@return	string
	 */
	public static function currentGround()
	{
		$bt = debug_backtrace();

		logit('called stancer', print_r($bt, true));
	}

}

