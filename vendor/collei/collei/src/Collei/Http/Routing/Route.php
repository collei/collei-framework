<?php 
namespace Collei\Http\Routing;

use Collei\App\App;
use Collei\Http\Routing\RouteResolver;
use Collei\Support\Str;

/**
 *	Encapsulates route features and tasks
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-07-xx
 */
class Route
{
	/**
	 *	@var array $route_methods
	 */
	private $route_methods = [];

	/**
	 *	@var string $route_path
	 */
	private $route_path = '';

	/**
	 *	@var string $route_path_stringable
	 */
	private $route_path_stringable = '';

	/**
	 *	@var string $route_assigned_path
	 */
	private $route_assigned_path = '';

	/**
	 *	@var string $route_naive_path
	 */
	private $route_naive_path = '';

	/**
	 *	@var string $route_is_variable
	 */
	private $route_is_variable = false;

	/**
	 *	@var string $route_owner
	 */
	private $route_owner = '';

	/**
	 *	@var string $servlet_path
	 */
	private $servlet_path = '';

	/**
	 *	@var string $servlet_class
	 */
	private $servlet_class = '';

	/**
	 *	@var string $servlet_method
	 */
	private $servlet_method = '';

	/**
	 *	@var string $name
	 */
	private $name = '';

	/**
	 *	@var array $middlewares
	 */
	private $middlewares = array();

	/**
	 *	Prospects servlet ground (physical server path)
	 *
	 *	@return	void
	 */
	private function groundizePath()
	{
		$this->servlet_path = RouteResolver::groundizeServletPath(
			$this->servlet_class
		);
	}

	/**
	 *	Creates a new instance of the class
	 *
	 *	@param	mixed	$routeMethod
	 *	@param	string	$path
	 *	@param	string	$servletClass
	 *	@param	string	$servletMethod
	 *	@param	string	$appName
	 */
	public function __construct(
		$routeMethod,
		string $path,
		string $servletClass,
		string $servletMethod = null,
		string $appName = null
	) {
		if (is_array($routeMethod)) {
			foreach ($routeMethod as $m) {
				$this->route_methods[] = strtoupper($m);
			}
		} else {
			$this->route_methods[] = strtoupper(('' . $routeMethod . ''));
		}
		//
		$this->route_path = $path;
		$this->route_assigned_path = $path;
		$this->route_naive_path = $path;
		$this->route_is_variable = preg_match('/.*(\/\{[\w\-]*\}\/).*/', $path); 
		$this->route_owner = $appName ?? PLAT_NAME;
		$this->servlet_path = $path;
		$this->servlet_class = $servletClass;
		$this->servlet_method = $servletMethod;
		//
		$this->route_path_stringable = $this->appendRootFolder($path);
		//
		$this->groundizePath();
	}

	/**
	 *	Returns route path with base folder as configured through /plat/conf/.app directives
	 *
	 *	@return	string
	 */
	private function appendRootFolder(string $path)
	{
		$result = PLAT_ROOT
			. App::getInstance()->getRootFolderAppended($path);
		//
		//logit('eiir: ' . __METHOD__, print_r([$path, $result], true));
		//
		return $result;
	}

	/**
	 *	Converts to string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->route_path_stringable;
	}

	/**
	 *	@property string $name
	 *	@property string $path
	 *	@property string $assignedPath
	 *	@property string $site
	 */
	public function __get($name)
	{
		if ($name == 'name') {
			return $this->name;
		}
		if ($name == 'path') {
			return $this->route_path;
		}
		if ($name == 'assignedPath') {
			return $this->route_assigned_path;
		}
		if ($name == 'site') {
			return $this->route_owner;
		}
		if ($name == 'isVariable') {
			return $this->route_is_variable;
		}
		return '';
	}

	/**
	 *	Assigns a name to the Route
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Http\Routing\Route
	 */
	public function name(string $name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 *	Assigns the method name
	 *
	 *	@param	string	$servletMethodName
	 *	@return	\Collei\Http\Routing\Route
	 */
	public function servletMethod(string $servletMethodName)
	{
		$this->servlet_method = $servletMethodName;
		return $this;
	}

	/**
	 *	Assigns middlewares to the Route
	 *
	 *	@param	string	$middleware
	 *	@return	\Collei\Http\Routing\Route
	 */
	public function middleware(string $middleware)
	{
		$this->middlewares[] = $middleware;
		return $this;
	}

	/**
	 *	Assigns values to the route variable names
	 *
	 *	@param	mixed	...$arguments
	 *	@return	\Collei\Http\Routing\Route
	 */
	public function assign(...$arguments)
	{
		$path = $this->route_path;
		//
		foreach ($arguments as $argument) {
			if (is_array($argument)) {
				foreach ($argument as $n => $v) {
					if (Str::has('{' . $n . '}', $path)) {
						$path = Str::replace('{' . $n . '}', $v, $path);
					}
				}
			} else {
				$named = Str::getNamedArg($path, 1, '{', '}');
				//
				if (empty($named)) {
					break;
				}
				//
				$path = Str::replace('{' . $named . '}', $argument, $path);
			}
		}
		//
		$this->route_assigned_path = $path;
		return $this;
	}

	/**
	 *	Returns the assigned path of the Route
	 *
	 *	@return	string
	 */
	public function getAssigned()
	{
		return $this->appendRootFolder($this->route_assigned_path);
	}

	/**
	 *	Returns whether the given $uri matches to the Route
	 *
	 *	@param	string	$uri
	 *	@param	string	$httpMethod
	 *	@return	bool
	 */
	public function matches(string $uri, string $httpMethod)
	{
		$bing = RouteResolver::matchesURI($this, $uri) && in_array(
			$httpMethod, $this->route_methods
		);
		//
		return $bing;
	}

	/**
	 *	Returns the Route path
	 *
	 *	@return	string
	 */
	public function getPath()
	{
		return $this->route_path;
	}

	/**
	 *	Returns the servlet path tied to this Route
	 *
	 *	@return	string
	 */
	public function getServletPath()
	{
		return $this->servlet_path;
	}

	/**
	 *	Returns the servlet class name
	 *
	 *	@return	string
	 */
	public function getServletClass()
	{
		return $this->servlet_class;
	}

	/**
	 *	Returns the servlet method name
	 *
	 *	@return	string
	 */
	public function getServletMethod()
	{
		return $this->servlet_method;
	}

}

