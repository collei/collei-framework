<?php
namespace Collei\Http\Routing;

use Collei\App\App;
use Collei\Http\Request;
use Collei\Http\Routing\Route;

/**
 *	Embodies router resolving
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class RouteResolver
{
	/**
	 *	@var @static bool $initialized
	 */
	private static $initialized = FALSE;

	/**
	 *	@var @static \Collei\App\App $app_instance
	 */
	private static $app_instance = null;

	/**
	 *	@var @static \Collei\Http\Request $app_request
	 */
	private static $app_request = null;

	/**
	 *	@var @static string $route_url_base
	 */
	private static $route_url_base = null;

	/**
	 *	@var @static string $route_root
	 */
	private static $route_root = '';

	/**
	 *	@var @static array $route_list
	 */
	private static $route_list = array();

	/**
	 *	@var @static array $route_default
	 */
	private static $route_default = array();

	/**
	 *	Initializes the static structure when needed
	 *
	 *	@static
	 *	@return	void
	 */
	private static function init()
	{
		if (!self::$initialized)
		{
			$app = App::getInstance();
			if (!is_null($app))
			{
				self::$app_instance = $app;
				self::$app_request = $app->getRequest();
				self::$route_url_base = $app->getConfigParam('sites.urlbase', '');
				self::$initialized = TRUE;
			}
		}
	}

	/**
	 *	Seeks for and tries to establish the servlet class ground (i.e., OS underlying path)
	 *
	 *	@static
	 *	@param	string	$servletClass
	 *	@return	string
	 */
	public static function groundizeServletPath(string $servletClass)
	{
		self::init();
		$ds = DIRECTORY_SEPARATOR;
		$app_servlet_path = '';
		//
		if (!is_null(self::$app_request)) {
			$app_root_uri = self::$app_instance->getRootURI();
			$app_servlet_path = PLAT_GROUND
				. DIRECTORY_SEPARATOR . $app_root_uri
				. DIRECTORY_SEPARATOR . $servletClass . PLAT_CLASSES_SUFFIX;
		} else {
			$app_servlet_path = PLAT_GROUND
				. DIRECTORY_SEPARATOR . $servletClass . PLAT_CLASSES_SUFFIX;
		}
		//
		$app_servlet_path = str_replace('/', $ds, $app_servlet_path);
		$app_servlet_path = str_replace("$ds$ds$ds$ds", $ds, $app_servlet_path);
		$app_servlet_path = str_replace("$ds$ds$ds", $ds, $app_servlet_path);
		$app_servlet_path = str_replace("$ds$ds", $ds, $app_servlet_path);
		//
		return $app_servlet_path;
	}

	/**
	 *	returns all Routes
	 *
	 *	@static
	 *	@return	array
	 */
	public static function all()
	{
		return self::$route_list;
	}

	/**
	 *	Creates and adds a new Route. Returns the created Route
	 *
	 *	@static
	 *	@param	mixed	$routeMethod
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@param	string	$appName
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function makeRoute(
		$routeMethod,
		string $path,
		string $servletClass,
		string $servletMethod = null,
		string $appName = null
	) {
		self::init();
		//
		$pathPrefix = (empty($appName)
			? PLAT_SITES_BASEURL 
			: (PLAT_SITES_BASEURL . '/' . $appName)
		);
		//
		$route = new Route(
			$routeMethod,
			$pathPrefix . $path,
			$servletClass,
			$servletMethod,
			$appName
		);
		self::$route_list[] = $route;
		return $route;
	}

	/**
	 *	Creates and adds a Default Route for any request not matched by
	 *	not matched by any other route
	 *
	 *	@static
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@param	string	$appName
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function makeDefaultRoute(
		string $servletClass,
		string $servletMethod = null,
		string $appName = null
	) {
		self::init();
		$route = new Route(
			Router::verbs(), '*', $servletClass, $servletMethod, $appName
		);
		self::$route_default[ $appName ?? PLAT_NAME ] = $route;
		return $route;
	}

	/**
	 *	Performs URI matching with the given Route
	 *
	 *	@static
	 *	@param	\Collei\Http\Routing\Route	$route
	 *	@param	string	$uri
	 *	@return	bool
	 */
	public static function matchesURI(Route $route, string $uri)
	{
		$str_route = $route->getPath();
		$str_uri = $uri;
		$prefix = self::$route_url_base;
		//
		// if strictly equals, no parameters found
		if ($str_route === $str_uri) {
			logit('::route.found.verbatim', $uri);
			return true;
		}
		//
		// if equals, ignoring the trailing / at request uri
		if ($str_route === substr($str_uri,0,-1)) {
			logit('::route.found', $uri);
			return true;
		}
		//
		$pattern = fetch_uri_pattern($str_route);
		$res = preg_match($pattern, $str_uri);
		//
		return $res;
	}

	/**
	 *	Returns the default Route associated to the specified app name
	 *
	 *	@static
	 *	@param	string	$appName
	 *	@return	\Collei\Http\Routing\Route|null
	 */
	protected static function getDefaultRouteFor(string $appName = null)
	{
		$appName = $appName ?? PLAT_NAME;
		//
		if (array_key_exists($appName, self::$route_default)) {
			return self::$route_default[$appName];
		}
		//
		if (array_key_exists(PLAT_NAME, self::$route_default)) {
			return self::$route_default[PLAT_NAME];
		}
		//
		return null;
	} 

	/**
	 *	Resolves the URI and method to a route, if it can be done
	 *
	 *	@static
	 *	@param	string	$uriOrName
	 *	@param	string	$httpMethod = "GET"
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function resolve(string $uriOrName, string $httpMethod = 'GET')
	{
		foreach (self::$route_list as $route_item) {
			if ($route_item->name === $uriOrName) {
				return $route_item;
			} elseif ($route_item->matches($uriOrName, $httpMethod)) {
				return $route_item;
			}
		}
		//
		return self::getDefaultRouteFor(
			self::$app_instance->getSite()
		);
	}

	/**
	 *	Resolves route by the route name
	 *
	 *	@static
	 *	@param	string	$routeName
	 *	@return	\Collei\Http\Routing\Route|null
	 */
	public static function resolveByName(string $routeName)
	{
		foreach (self::$route_list as $route_item) {
			if ($route_item->name == $routeName) {
				return $route_item;
			}
		}
		//
		return self::getDefaultRouteFor(
			self::$app_instance->getSite()
		);
	}

}


