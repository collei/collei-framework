<?php 
namespace Collei\Http\Routing;

use Collei\App\App;
use Collei\Http\Routing\Route;
use Collei\Http\Routing\RouteResolver;
use Collei\Utils\Arr;
use Collei\Utils\Str;
use Closure;

/**
 *	Embodies router capabilities
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Router
{

	/*----------------------------------*\
	 *	route group support: backstage	*
	\*----------------------------------*/

	/**
	 *	@static @var array $groupLevels
	 */
	private static $groupLevels = [];

	/**
	 *	@static @var int $groupLevel
	 */
	private static $groupLevel = -1;

	/**
	 *	Returns the current running group for the running closure
	 *	called by group() method
	 *  
	 *	@static
	 *	@return	mixed
	 */
	private static function getActiveGroup()
	{
		if (static::$groupLevel >= 0) {
			return static::$groupLevels[static::$groupLevel];
		}
		//
		return false;
	}

	/**
	 *	Sets an attribute in the current, running group
	 *
	 *	@static
	 *	@param	string	$term
	 *	@param	mixed	$value
	 *	@return	mixed
	 */
	private static function setInActiveGroup(string $term, $value)
	{
		if (!in_array($term, ['prefix','name','controller'])) {
			if (static::$groupLevel >= 0) {
				static::$groupLevels[static::$groupLevel][$term] = $value;
			}
		}
	}

	/**
	 *	Returns the array of values of a given attribute across levels,
	 *	ignoring nulls. 
	 *
	 *	@static
	 *	@param	string	$term
	 *	@return	array|bool
	 */
	private static function getAccumulated(string $term)
	{
		if (!in_array($term, ['prefix','name','controller'])) {
			return false;
		}
		//
		if (static::$groupLevel >= 0) {
			$termLevels = [];
			//
			foreach (static::$groupLevels as $level) {
				if (isset($level[$term])) {
					$termLevels[] = $level[$term];
				}
			}
			//
			return $termLevels;
		}
		//
		return false;
	}

	/**
	 *	Returns the accumulated route path across levels, ignoring nulls. 
	 *
	 *	@static
	 *	@return	string|bool
	 */
	private static function getAccumulatedPath()
	{
		if ($pathLevels = static::getAccumulated('prefix')) {
			return Arr::joinCollapsed('/', $pathLevels);
		}
		//
		return false;
	}

	/**
	 *	Returns the accumulated route name across levels, ignoring nulls. 
	 *
	 *	@static
	 *	@return	string|bool
	 */
	private static function getAccumulatedName()
	{
		if ($pathLevels = static::getAccumulated('name')) {
			return Arr::joinCollapsed('', $pathLevels);
		}
		//
		return false;
	}

	/**
	 *	Returns the topmost controller (latest added) in the level stack 
	 *
	 *	@static
	 *	@return	mixed
	 */
	private static function getGroupController()
	{
		if ($pathLevels = static::getAccumulated('controller')) {
			return array_pop($pathLevels) ?? false;
		}
		//
		return false;
	}

	/**
	 *	@static @var array $groupAttributes
	 */
	private $groupAttributes = [];

	/**
	 *	Builds the cell for calling that closure from  
	 *
	 */
	private function __construct(array $attributes)
	{
		static::$groupLevels[++static::$groupLevel] = $attributes;
		//
		$this->groupAttributes = $attributes;
	}

	/**
	 *	Erases the cell that called that closure
	 *
	 */
	public function __destruct()
	{
		array_pop(static::$groupLevels);
		//
		--static::$groupLevel;
	}

	/*----------------------------------*\
	 *	route group support: the stage	*
	\*----------------------------------*/

	/**
	 *	Builds a new group with the given controller (Servlet)
	 *
	 *	@static
	 *	@param	mixed	$controllerClass
	 *	@return	\Collei\Http\Routing\Router
	 */
	public static function controller($controllerClass)
	{
		return new static([
			'controller' => $controllerClass
		]);
	}

	/**
	 *	Builds a new group with the given path prefix
	 *
	 *	@static
	 *	@param	string	$prefix
	 *	@return	\Collei\Http\Routing\Router
	 */
	public static function prefix(string $prefix)
	{
		return new static([
			'prefix' => $prefix
		]);
	}

	/**
	 *	Builds a new group with the given name prefix
	 *
	 *	@static
	 *	@param	string	$name
	 *	@return	\Collei\Http\Routing\Router
	 */
	public static function name(string $name)
	{
		return new static([
			'name' => $name
		]);
	}

	/**
	 *	Builds a new group with the given attributes in associative array
	 *
	 *	@static
	 *	@param	array	$arguments
	 *	@return	\Collei\Http\Routing\Router
	 */
	public static function by(array $arguments)
	{
		return new static(
			Arr::filterByKey($arguments, ['controller','prefix','name'])
		);
	}

	/**
	 *	Sets the controller to the current group before starting it
	 *
	 *	@param	mixed	$prefix
	 *	@return	\Collei\Http\Routing\Router
	 */
	public function andController($controller)
	{
		self::setInActiveGroup('controller', $controller);
		//
		return $this;
	}

	/**
	 *	Sets the path prefix to the current group before starting it
	 *
	 *	@param	string	$prefix
	 *	@return	\Collei\Http\Routing\Router
	 */
	public function andPrefix(string $prefix)
	{
		self::setInActiveGroup('prefix', $prefix);
		//
		return $this;
	}

	/**
	 *	Sets the name prefix to the current group before starting it
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Http\Routing\Router
	 */
	public function andName(string $name)
	{
		self::setInActiveGroup('name', $name);
		//
		return $this;
	}

	/**
	 *	Runs the group bubble in the current group context
	 *
	 *	@param	\Closure	$routeGroup
	 *	@return	mixed
	 */
	public function group(Closure $routeGroup)
	{
		return $routeGroup() ?? null;
	}

	/*----------------------*\
	 *
	\*----------------------*/

	/**
	 *	Scan caller file directory info
	 *
	 *	@return	void
	 */
	private static function fetchBaseAppCaller()
	{
		$bt =  debug_backtrace();
		$ca_file = $bt[2]['file'];
		$ca_line = $bt[2]['line'];

		// 'route.php' stelkani ha pefa
		// the folder in which the 'route.php' file is
		$folder_parent = basename(dirname($ca_file));

		// "'route.php' stelkani ha pefa"-ni ha pefa
		// the folder in which "the folder in which the 'route.php' file is" is
		$folder_grandpa = basename(dirname(dirname($ca_file)));

		if (
			($folder_grandpa == PLAT_FOLDER) &&
			($folder_parent == PLAT_APP_FOLDER_NAME)
		) {
			return null;
		}
		return $folder_parent;
	}

	/**
	 *	Internal helper to create a route with grouping support
	 *
	 *	@static
	 *	@param	mixed	$routeMethod
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function makeRoute(
		$routeMethod,
		string $path,
		string $servletClass,
		string $servletMethod = null
	) {
		$appName = self::fetchBaseAppCaller();
		//
		if ($attr = self::getActiveGroup()) {
			if (($o_path = self::getAccumulatedPath())) {
				$o_path .= '/' . Str::trimPrefix($path, '/');
			} else {
				$o_path = $path;
			}
			//
			if (!Str::startsWith($o_path, '/')) {
				$o_path = '/' . $o_path;
			}
			//
			$o_method = $servletClass;
			//
			if (!$o_controller = self::getGroupController()) {
				$o_method = $servletMethod;
				$o_controller = $servletClass;
			}
			//
			$routeDescription = '('
				. (is_array($routeMethod) ? implode(',', $routeMethod) : $routeMethod)
				. ")({$o_controller}::{$o_method})";
			//
			/*
			logit(__METHOD__, print_r([
				'route' => $routeDescription,
				'path' => $path,
				'cold' => $o_path,
			], true));
			*/
			//
			return RouteResolver::makeRoute(
				$routeMethod, $o_path, $o_controller, $o_method, $appName
			);
		}
		//
		return RouteResolver::makeRoute(
			$routeMethod, $path, $servletClass, $servletMethod, $appName
		);
	}


	/**
	 *	Return all possible HTTP method verbs
	 *
	 *	@return	array
	 */
	public static function verbs()
	{
		return ['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS','CONNECT'];
	}

	/**
	 *	Register a GET route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function get(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute(['GET','HEAD'], $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a POST route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function post(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('POST', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a PUT route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function put(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('PUT', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a PATCH route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function patch(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('PATCH', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a DELETE route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function delete(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('DELETE', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a HEAD route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function head(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('HEAD', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a OPTIONS route
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function options(string $path, string $servletClass, string $servletMethod = null)
	{
		return self::makeRoute('OPTIONS', $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a single route for the specified HTTP verbs
	 *
	 *	@param	array	$verbs
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function match(array $verbs, string $path, string $servletClass, string $servletMethod)
	{
		return self::makeRoute($verbs, $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a generic route (for ALL HTTP verbs)
	 *
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function any(string $path, string $servletClass, string $servletMethod)
	{
		return self::makeRoute(self::verbs(), $path, $servletClass, $servletMethod);
	}

	/**
	 *	Register a default route to be invoked whenever an inexistent URI is requested
	 *
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@param	string	$site
	 *	@return	\Collei\Http\Routing\Route
	 */
	public static function default(string $servletClass, string $servletMethod, string $site = null)
	{
		return RouteResolver::makeDefaultRoute($servletClass, $servletMethod, $site);
	}

}

