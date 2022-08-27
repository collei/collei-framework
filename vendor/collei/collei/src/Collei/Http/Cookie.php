<?php
namespace Collei\Http;


/**
 *	Encapsulates a HTTP cookie
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
class Cookie
{
	/**
	 *	@var string $name
	 */
	private $name;

	/**
	 *	@var string $value
	 */
	private $value;

	/**
	 *	@var int $expires
	 */
	private $expires;

	/**
	 *	@var string $path
	 */
	private $path;

	/**
	 *	@var string $domain
	 */
	private $domain;

	/**
	 *	@var bool $secure
	 */
	private $secure;

	/**
	 *	@var bool $httpOnly
	 */
	private $httpOnly;

	/**
	 *	@var string $sameSite
	 */
	private $sameSite;

	/**
	 *	Builds a Cookie instance
	 *
	 *	@param string $name
	 *	@param string $value
	 *	@param int $expires
	 *	@param string $path
	 *	@param string $domain
	 *	@param bool $secure
	 *	@param bool $httpOnly
	 *	@param string $sameSite
	 */
	public function __construct(string $name, string $value = '', int $expires = 0, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = false, string $sameSite = 'Lax')
	{
		$this->name = $name;
		$this->value = $value;
		$this->expires = $expires;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->httpOnly = $httpOnly;
		//
		$sameSite = ucfirst(strtolower($sameSite));
		//
		if (in_array(strtolower($sameSite), ['Lax', 'Strict', 'None'])) {
			$this->sameSite = $sameSite;
		} else {
			$this->sameSite = 'Lax';
		}
	}

	/**
	 *	Publishes certain properties
	 *
	 */
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}
		//
		return '';
	}

	/**
	 *	Outputs the cookie as HTTP header for the browser
	 *
	 *	@return	void
	 */
	public function output()
	{
		$options = [
			'expires' => $this->expires,
			'path' => $this->path,
			'domain' => $this->domain,
			'secure' => $this->secure,
			'httponly' => $this->httpOnly,
			'samesite' => $this->sameSite
		];
		//
		setcookie($this->name, $this->value, $options);
	}

	/**
	 *	Performs server-side cookie removal
	 *
	 *	@return	void
	 */
	public function remove()
	{
		setcookie($this->name, '', time() - 43200);
	}

}


