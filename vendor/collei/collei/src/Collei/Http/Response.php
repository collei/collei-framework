<?php 
namespace Collei\Http;

use Collei\Utils\Collections\Properties;

/**
 *	Encapsulates the servlet response
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
class Response
{
	/**
	 *	@var \Collei\Utils\Collections\Properties $headers
	 */
	private $headers = null;

	/**
	 *	@var \Collei\Utils\Collections\Properties $attributes
	 */
	private $attributes = null;

	/**
	 *	@var array $cookies
	 */
	private $cookies = [];

	/**
	 *	@var array $cookiesForRemoval
	 */
	private $cookiesForRemoval = [];

	/**
	 *	@var string $body
	 */
	private $body = '';

	/**
	 *	performs initialization
	 *
	 *	@return	void
	 */
	protected function initialize()
	{
		$this->headers = new Properties();
		$this->attributes = new Properties();
	}

	/**
	 *	Performs header output
	 *
	 *	@return	void
	 */
	protected function outputHeaders()
	{
		$headers = $this->headers->asArray();

		foreach ($headers as $name => $value)
		{
			header("$name: $value", false);
		}
	}

	/**
	 *	Performs cookie output
	 *
	 *	@return	void
	 */
	protected function outputCookies()
	{
		foreach ($this->cookies as $acookie)
		{
			if (in_array($acookie->name, $this->cookiesForRemoval))
			{
				$acookie->remove();
			}
			else
			{
				$acookie->output();
			}
		}
	}

	/**
	 *	Set a cookie for cleanup
	 *
	 *	@return	void
	 */
	public function removeCookie(string $which)
	{
		$this->cookiesForRemoval[$which] = $which;
	}

	/**
	 *	Set cookies for cleanup
	 *
	 *	@return	void
	 */
	public function removeCookies(string ...$whose)
	{
		if (!empty($whose))
		{
			foreach ($whose as $which)
			{
				$this->removeCookie($which);
			}
		}
	}

	/**
	 *	Performs content output
	 *
	 *	@return	void
	 */
	protected function outputBody()
	{
		echo $this->body;
	} 

	/**
	 *	Builds and instantiates the class
	 *
	 */
	protected function __construct()
	{
		$this->initialize();
	}

	/**
	 *	Instance factory
	 *
	 *	@return	\Collei\Http\Response
	 */
	public static function make()
	{
		return new static();
	}

	/**
	 *	Set a header
	 *
	 *	@param	string	$name
	 *	@param	string	$value
	 *	@return	\Collei\Http\DataResponse
	 */
	public function setHeader(string $name, string $value)
	{
		$this->headers->add($name, $value);

		return $this;
	}

	/**
	 *	Defines the specified attribute
	 *
	 *	@param	string	$name
	 *	@param	string	$value
	 *	@return	\Collei\Http\DataResponse
	 */
	public function setAttribute(string $name, string $value)
	{
		$this->attributes->add($name, $value);

		return $this;
	}

	/**
	 *	Returns the specified attribute value
	 *
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public function getAttribute(string $name)
	{
		return $this->attributes->get($name);
	}

	/**
	 *	Defines headers at once
	 *
	 *	@param	array	$values
	 *	@return	\Collei\Http\DataResponse
	 */
	public function setHeaders(array $values)
	{
		$this->headers->adds($values);

		return $this;
	}

	/**
	 *	Performs redirect
	 *
	 *	@param	string	$name
	 *	@param	string	$value
	 *	@param	int		$expires
	 *	@return	void
	 */
	public function setCookie(string $name, string $value, int $expires = null)
	{
		if(is_null($expires))
		{
			$expires = time() + (24 * 60 * 60);
		}

		unset($this->cookiesForRemoval[$name]);

		$this->cookies[$name] = new Cookie($name, $value, $expires);

		return $this;
	}

	/**
	 *	Defines content of the response body 
	 *
	 *	@param	mixed	$content
	 *	@return	\Collei\Http\DataResponse
	 */
	public function setBody($content)
	{
		$this->body = $content;

		return $this;
	}

	/**
	 *	Performs redirect
	 *
	 *	@param	mixed	$to
	 *	@return	void
	 */
	public function redirect($to)
	{
		@ob_end_clean();
		@ob_start();

		http_response_code(303);
		header('Location: ' . $to, true);
		
		die();
	}

	/**
	 *	Performs output
	 *
	 *	@return	void
	 */
	public function output(bool $removeCookies = false, string ...$which)
	{
		$this->outputHeaders();

		if ($removeCookies)
		{
			$this->removeCookies(...$which);
		}
		else
		{
			$this->outputCookies();
		}

		$this->outputBody();
	} 

}


