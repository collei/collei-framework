<?php
namespace Collei\Http\Filters;

use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Http\Session;

/**
 *	Describe and implement minimum basic features for application request filters 
 *
 *	@author <alarido.su@gmail.com>
 *	@version 1.0
 *	@abstract
 *	@copyright never
 */
abstract class Filter
{
	/**
	 *	@var	bool $enabled
	 */
	private $enabled = true;

	/**
	 *	@var	Collei\Http\Request $request
	 */
	private $request;

	/**
	 *	@var	Collei\Http\Session $response
	 */
	protected $response;

	/**
	 *	@var	Collei\Http\Session $session
	 */
	protected $session;

	/**
	 *	@var	Collei\Http\Request	$request
	 */
	public final function __get($name)
	{
		if ($name == 'request')
		{
			return $this->request;
		}
		if ($name == 'response')
		{
			return $this->response;
		}
		return;
	}

	/**
	 *	builds and initializes a filter instance
	 *
	 *	@param	Collei\Http\Request		the request to filter
	 *	@param	Collei\Http\Response	a response
	 */
	public final function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
		$this->session = Session::capture();
	}

	/**
	 *	runs the filter
	 *
	 *	@return mixed	bool(true) if the filter allows the request procceed.
	 *					Otherewise, you may return a view or a response, or even make a redirect
	 */
	public function filter()
	{
		return true;
	}

	/**
	 *	allows ignore this filter for certain requests
	 *
	 *	@return array 	List of rules in format '<verb> <uri>'. 
	 *					<verb> must be a valid HTTP verb or an asterisk.
	 *					<uri> must be a valid route URI or an asterisk.
	 *					Both are optional. An empty line or a '* *' (astyerisk + space + asterisk) line disables the filter.
	 *					Returning an empty array will enable the given filter to ALL requests
	 */
	public function except()
	{
		return [];
	}

	/**
	 *	turns the filter on
	 *
	 *	@return void
	 */
	public function enable()
	{
		$this->enabled = true;
	}

	/**
	 *	turns the filter off
	 *
	 *	@return void
	 */
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	 *	checks if the filter is turned on
	 *
	 *	@return void
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

}

