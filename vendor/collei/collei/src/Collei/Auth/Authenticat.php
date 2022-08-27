<?php
namespace Collei\Auth;

/**
 *	Authentication helpers
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 20211-074-xx
 */
class Authenticat
{
	/**
	 *	Performs password encoding
	 *
	 *	@param	string	$password
	 *	@return	string
	 */
	public static function passwordHash(string $password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 *	Performs check on the encoded password
	 *
	 *	@param	string	$password
	 *	@param	string	$hash
	 *	@return	string
	 */
	public static function passwordCheck(string $password, string $hash)
	{
		return password_verify($password, $hash);
	}

}


