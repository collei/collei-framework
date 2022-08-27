<?php
namespace Collei\Http;

/**
 *	Encapsulates URL properties
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-04-xx
 */
class URL
{
	/**
	 *	@var string $url
	 */
	private $url = '';

	/**
	 *	@var string $url_host
	 */
	private $url_host = '';

	/**
	 *	@var string $url_port
	 */
	private $url_port = 0;

	/**
	 *	@var string $url_path
	 */
	private $url_path = '';

	/**
	 *	@var string $url_query
	 */
	private $url_query = '';

	/**
	 *	@var string $url_fragment
	 */
	private $url_fragment = '';

	/**
	 *	Parses the URL
	 *
	 *	@return	void
	 */
	private function parse()
	{
		$url = $this->url;
		$components = parse_url($url);

		$this->url_host = isset($components['host']) ? $components['host'] : $_SERVER['SERVER_NAME'];
		$this->url_port = isset($components['port']) ? $components['port'] : $_SERVER['SERVER_PORT'];
		$this->url_path = isset($components['path']) ? $components['path'] : '/';
		$this->url_query = isset($components['query']) ? $components['query'] : '';
		$this->url_fragment = isset($components['fragment']) ? $components['fragment'] : '';
	}

	/**
	 *	Builds and initializes a new URL instance
	 *
	 *	@param	string	$url
	 */
	public function __construct(string $url)
	{
		$this->url = $url;
		$this->parse();
	}

	/**
	 *	@var	string	$url
	 *	@var	string	$url_host
	 *	@var	string	$url_port
	 *	@var	string	$url_path
	 *	@var	string	$url_query
	 *	@var	string	$url_fragment
	 */
	public function __get($name)
	{
		if ($name == 'url')
		{
			return $this->url;
		}

		$prop_name = "url_{$name}";
		if (property_exists($this, $prop_name))
		{
			return $this->$prop_name;
		}
	}

	/**
	 *	Returns the URL as string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->url;
	}

}


