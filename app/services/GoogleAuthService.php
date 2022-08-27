<?php

namespace App\Services;

use Collei\Services\Service;
use Collei\Utils\Arr;
use Collei\Utils\Str;
use Collei\Auth\AuthConfirmator;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

/**
 *	This allow reuse of code and funcionality injection.
 *	Basic capabilities available through base service.
 *
 */
class GoogleAuthService extends Service implements AuthConfirmator
{
	/**
	 *	@var string $secret
	 */
	private $secret = '';

	/**
	 *	@var string $qr
	 */
	private $qr = '';

	/**
	 *	@var  Sonata\GoogleAuthenticator\GoogleAuthenticator $secret
	 */
	private $goo = null;

	/**
	 *	Initializer
	 *
	 *	@return self
	 */
	public function __construct()
	{
		$this->goo = new GoogleAuthenticator();
	}

	/**
	 *	Returns the secret geneated by the MFA mechanism
	 *
	 *	@return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 *	Returns the QR Code URL geneated by the MFA mechanism
	 *
	 *	@return string
	 */
	public function getQrUrl()
	{
		return $this->qr;
	}

	/**
	 *	Takes the given $userName and generates a QRCode URL
	 *	from it coupled with the platform name and the
	 *	generated secret.
	 *
	 *	@param	string	$userName
	 *	@return string
	 */
	public function generateQrCodeURL(string $userName)
	{
		$this->secret = $this->goo->generateSecret();

		return $this->qr = GoogleQrUrl::generate(
			$userName, $this->secret, PLAT_NAME, 300
		);
	}

	/**
	 *	Check for the $code the user provided with the $secret
	 *	the system holds upon him. True if $code is valid,
	 *	false otherwise. 
	 *
	 *	@param	string	$secret
	 *	@param	string	$code
	 *	@return bool
	 */
	public function verify(string $secret, string $code)
	{
		return $this->goo->checkCode($secret, $code);
	}

}





