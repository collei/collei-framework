<?php
namespace Collei\App;

use Collei\Http\URL;

/**
 *	Embodies URL properties
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class AppURL extends URL 
{
	/**
	 *	@var string $app_folder
	 */
	private $app_folder = null;

	/**
	 *	@var string $app_root
	 */
	private $app_root = null;

	/**
	 *	@var string $app_ground
	 */
	private $app_ground = null;

	/**
	 *	@var bool $app_has_ground
	 */
	private $app_has_ground = false;

	/**
	 *	Performs ground deepness scanning
	 *
	 *	@return	void
	 */
	private function prospectRoot()
	{
		$path = $this->path;
		if (str_starts_with($path, '/')) {
			$path = substr($path, 1);
		}
		//
		$path_parts = explode('/', $path, 3);
		if (@count($path_parts) == 3) {
			$this->app_folder = $path_parts[1];
			$this->app_root = '/' . $path_parts[0] . '/' . $path_parts[1];
		} else {
			$this->app_folder = null;
			$this->app_root = '/';
		}
	}

	/**
	 *	Performs ground scanning
	 *
	 *	@return	void
	 */
	private function locateGround()
	{
		$request_uri = $this->url;
		//
		if (is_null($this->app_root)) {
			logerror(
				'app_not_loadable',
				"App could not understand this request path: $request_uri "
			);
			return;
		}
		//
		$fldr = $this->app_folder;
		$flroot = $this->app_root;
		$ground = PLAT_GROUND . str_replace('/', DIRECTORY_SEPARATOR, $this->app_root);
		if (!is_dir($ground) && !is_dir(dirname($ground))) {
			logerror(
				'app_not_loadable',
				"There is no App set for handling this request path:\r\n"
					. "\r\n\t'$request_uri'\r\n\t\ton $ground"
					. "\r\n\t\ton $fldr\r\n\t\twithin $flroot"
			);
			return;
		}
		//
		$this->app_ground = $ground;
		$this->app_has_ground = true;
	}

	/**
	 *	Builds a new instance
	 *
	 *	@param	string	$url
	 */
	public function __construct(string $url)
	{
		parent::__construct($url);
		$this->prospectRoot();
		$this->locateGround();
	}

	/**
	 *	Returns if the URL ground -- related to the current server -- exists
	 *
	 *	@return	string
	 */
	public function hasGround()
	{
		return $this->app_has_ground;
	}

	/**
	 *	Returns the possible correspondent folder at the current server
	 *
	 *	@return	string
	 */
	public function getFolder()
	{
		return $this->app_folder;
	}

	/**
	 *	Returns the possible URL root at the current server
	 *
	 *	@return	string
	 */
	public function getRoot()
	{
		return $this->app_root;
	}

	/**
	 *	Returns the possible URL ground at the current server
	 *
	 *	@return	string
	 */
	public function getGround()
	{
		return $this->app_ground;
	}

	/**
	 *	Static factory of instances
	 *
	 *	@param	string	$url
	 *	@return	\Collei\App\AppURL
	 */
	public static function make(string $url)
	{
		return new static($url);
	}

}

