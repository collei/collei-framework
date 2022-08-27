<?php

namespace Collei\Auth;

/**
 *	Authentication Confirmators
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-07-27
 */
interface AuthConfirmator
{
	/**
	 *	Returns the secret geneated by the MFA mechanism
	 *
	 *	@return string
	 */
	public function getSecret();

	/**
	 *	Returns the QR Code URL geneated by the MFA mechanism
	 *
	 *	@return string
	 */
	public function getQrUrl();

	/**
	 *	Takes the given $userName and generates a QRCode URL
	 *	from it coupled with the platform name and the
	 *	generated secret.
	 *
	 *	@param	string	$userName
	 *	@return string
	 */
	public function generateQrCodeURL(string $userName);

	/**
	 *	Check for the $code the user provided with the $secret
	 *	the system holds upon him. True if $code is valid,
	 *	false otherwise. 
	 *
	 *	@param	string	$secret
	 *	@param	string	$code
	 *	@return bool
	 */
	public function verify(string $secret, string $code);

}

