<?php 
namespace Collei\Http;

use Collei\App\App;
use Collei\Utils\Collections\Properties;
use Collei\Utils\Parsers\RawRequestBodyParser;
use Collei\Utils\Str;
use Collei\Http\Routing\Route;
use Collei\Http\Routing\Routeable;
use Collei\Pacts\Capturable;
use Collei\Http\Traits\MimeTypes;

/**
 *	Encapsulates a HTTP request
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-05-xx
 */
class Request implements Routeable, Capturable
{
	use MimeTypes;

	/**
	 *	@var \Collei\Utils\Collections\Properties $attributes
	 */
	private $attributes;

	/**
	 *	@var \Collei\Utils\Collections\Properties $query
	 */
	private $query;

	/**
	 *	@var \Collei\Utils\Collections\Properties $forms
	 */
	private $form;

	/**
	 *	@var \Collei\Utils\Collections\Properties $cookies
	 */
	private $cookies;

	/**
	 *	@var \Collei\Utils\Collections\Properties $headers
	 */
	private $headers;

	/**
	 *	@var \Collei\Utils\Collections\Properties $server
	 */
	private $server;

	/**
	 *	@var \Collei\Utils\Collections\Properties $server
	 */
	private $uriRootFolder = '/';

	/**
	 *	Reaps data from php://input for AJAX PUT requests
	 *
	 *	@return	bool
	 */
	private function reapPutData()
	{
		$verb = strtoupper($this->server->get('REQUEST_METHOD'));
		//
		if (!in_array($verb, ['GET','HEAD']))
		{
			$put_vars = (
				new RawRequestBodyParser(file_get_contents("php://input"))
			)->getFields()->asArray();

			if (!empty($put_vars))
			{
				$this->form->adds($put_vars, true);
				//
				return true;
			}
		}
		//
		return false;
	}

	/**
	 *	Returns an array of HTTP header values
	 *
	 *	@return	array
	 */
	private function extractHeaderDataFromServerVariables()
	{
		$http_headers = [];
		$capitalize_first = function($str)
		{
			return strtoupper(substr($str,0,1)) . strtolower(substr($str,1));
		};

		foreach ($_SERVER as $n => $v)
		{
			if (str_starts_with($n, 'HTTP_'))
			{
				$arr_n = explode('_',$n);
				$arr_n = array_map($capitalize_first, $arr_n);
				$transformed_n = implode('-',$arr_n);

				$http_headers[$transformed_n] = $v;
			}
		}

		return $http_headers;
	}

	/**
	 *	Copies and modifies data from $_SERVER variable
	 *
	 *	@return	array
	 */
	private function preProcessServerVars()
	{
		$server_vars = [];

		foreach ($_SERVER as $n => $v)
		{
			if ($n == 'REQUEST_URI')
			{
				if (Str::startsWith($v, '/plat/'))
				{
					$server_vars[$n] = '/' . substr($v, 6);
					/*
					 *	observes root folder on requests and routes
					 */
					$this->uriRootFolder = '/plat/';
				}
				else
				{
					$server_vars[$n] = $v;
				}
			}
			else
			{
				$server_vars[$n] = $v;
			}
		}

		return $server_vars;
	}

	/**
	 *	Obtains request data from server variables
	 *
	 *	@return	void
	 */
	private function gatherData()
	{
		$this->attributes = new Properties();
		$this->form = new Properties($_POST);
		$this->query = new Properties($_GET);
		$this->cookies = new Properties($_COOKIE);
		$this->server = new Properties($this->preProcessServerVars());
		$this->headers = new Properties($this->extractHeaderDataFromServerVariables());
		$this->reapPutData();
	}

	/**
	 *	@var string $request_method
	 */
	private $request_method = '';

	/**
	 *	@var string $request_uri
	 */
	private $request_uri = '';

	/**
	 *	@var string $request_path
	 */
	private $request_path = '';

	/**
	 *	@var \Collei\Http\Routing\Route $route
	 */
	private $route = null;

	/**
	 *	Extract data from URI through route parameters
	 *
	 *	@return	void
	 */
	private function inferRouteParameters()
	{
		$path_request = $this->path;
		$path_route = $this->route->getPath();
		$route_params = fetch_uri_parameters($path_request, $path_route);

		if (!is_null($route_params))
		{
			$this->attributes->adds($route_params);
		}
	}

	/**
	 *	Initializes with data retrieved from server
	 *
	 *	@return	void
	 */
	private function initialize()
	{
		// retrieves data from the request and the server altogether
		$this->gatherData();

		// request method, uri and path, too
		$this->request_method = strtoupper($this->server->get('REQUEST_METHOD'));
		$this->request_uri = $this->server->get('REQUEST_URI');
		$this->request_path = $this->request_uri;

		// allow redefine the request verb for other than GET/POST
		// by using @method('METHOD') inside the form in a view,
		// so they can match its related route 
		if ($this->request_method === 'POST')
		{
			if ($this->form->has('_method'))
			{
				$this->request_method = $this->form->get('_method');
			}
		}

		// gathers and preserves the query string, if any,
		// so it can be used later, if needed.
		if (($question = strpos($this->request_path, '?')) !== false)
		{
			$this->request_path = substr($this->request_path, 0, $question);
		}
	}

	/**
	 *	Builds and initializes with data captured from the web server
	 *
	 */
	protected function __construct()
	{
		// performs request data capture and processing
		$this->initialize();
	}

	/**
	 *	Captures the request from the server as a Request instance
	 *
	 *	@return	instanceof \Collei\Http\Request
	 */
	public static function capture()
	{
		return new static();
	}

	/**
	 *	Sets the specififed attribute
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public function setAttribute(string $name, $value)
	{
		$this->attributes->set($name, $value);
	}

	/**
	 *	Returns the specified attribute
	 *
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public function getAttribute(string $name)
	{
		return $this->attributes->get($name);
	}

	/**
	 *	Tells if the given path matches to the bound Route
	 *
	 *	@param	string	$toCompare
	 *	@return	bool
	 */
	public function isRoutePath(string $toCompare)
	{
		if ($this->bound())
		{
			return $toCompare === $this->route->path;
		}
		return false;
	} 

	/**
	 *	@property	string	$uri
	 *	@property	string	$path
	 *	@property	string	$routePath
	 *	@property	string	$routeSite
	 *	@property	string	$method
	 */
	public function __get($name)
	{
		if ($name == 'uri')
		{
			return $this->request_uri;
		}
		if ($name == 'path')
		{
			return $this->request_path;
		}
		if ($name == 'routePath')
		{
			if ($this->bound())
			{
				return $this->route->path;
			}
			return '';
		}
		if ($name == 'routeSite')
		{
			if ($this->bound())
			{
				return $this->route->site;
			}
			return '';
		}
		if ($name == 'method')
		{
			return $this->request_method;
		}
		//
		if ($this->hasParameter($name))
		{
			return $this->getParameter($name);
		}

		$trace = debug_backtrace();
		logerror(
			__CLASS__ . '->' . $name,
			'Undefined property ' . $name
				. ' in ' . $trace[0]['file']
				. ' on line ' . $trace[0]['line'] . '.',
			E_USER_NOTICE
		);
	}

	/**
	 *	Get the parameter value. Asks the URI parameters first,
	 *	then the form and, finally, the query string 
	 *
	 *	@param	string	$param	the parameter name
	 *	@return	mixed	returns the value if parameter exists, null otherwise
	 */
	public function getParameter(string $param)
	{
		if ($this->attributes->has($param))
		{
			return $this->attributes->get($param);
		}
		if ($this->form->has($param))
		{
			return $this->form->get($param);
		}
		if ($this->query->has($param))
		{
			return $this->query->get($param);
		}
		return null;
	}

	/**
	 *	Ask for parameter existence
	 *
	 *	@param	string	$param	the parameter name
	 *	@return	bool	true if it exists, false otherwise
	 *	@deprecated
	 */
	public function has(string $param)
	{
		return $this->hasParameter($param);
	}

	/**
	 *	Ask for parameter existence
	 *
	 *	@param	string	$param	the parameter name
	 *	@return	bool	true if it exists, false otherwise
	 */
	public function hasParameter(string $param)
	{
		return $this->attributes->has($param)
			|| $this->form->has($param)
			|| $this->query->has($param);
	}

	/**
	 *	Returns some cookie
	 *
	 *	@param	string	$item	the parameter name
	 *	@return	string
	 */
	public function cookie(string $item)
	{
		if ($this->cookies->has($item))
		{
			return $this->cookies->get($item);
		}
		return '';
	}

	/**
	 *	Binds this request to the specified route (interface Routeable::bindRoute)
	 *
	 *	@param	\Collei\Http\Routing\Route	$route
	 *	@return	void
	 */
	public function bindRoute(Route $route)
	{
		$this->route = $route;
		$this->inferRouteParameters();
	}

	/**
	 *	Returns if the request is already bound
	 *
	 *	@return	bool
	 */
	public function bound()
	{
		return !is_null($this->route);
	}

	/**
	 *	Returns true if the request URI matches the given pattern,
	 *	false otherwise
	 *
	 *	@param	string	$pattern
	 *	@param	mixed	$matches
	 *	@return	bool
	 */
	public function matches(string $pattern, array &$matches = null)
	{
		if (is_null($matches))
		{
			return preg_match($pattern, $this->uri) === 1;
		}

		return preg_match($pattern, $this->uri, $matches) === 1;
	}

	/**
	 *	Checks if the request URI refers to an existing resource.
	 *	If yes, loads and returns the content directly to the browser.
	 *
	 *	@param	string	$pattern
	 *	@param	mixed	$matches
	 *	@return	bool
	 */
	public static function processResourceful(Request $request)
	{
		$uri = $request->uri;
		$file = '';

		if ($request->matches('#^\/sites\/resources\/(.*)#'))
		{
			$file = \Collei\Utils\Str::trimPrefix($uri, '/sites');
			$file = grounded('..' . str_replace('/', DIRECTORY_SEPARATOR, $file));
		}
		elseif ($request->matches('#^\/sites\/([^\/]+)\/resources\/(.*)#'))
		{
			$file = str_replace('/', DIRECTORY_SEPARATOR, $uri);
			$file = grounded('..' . str_replace('/', DIRECTORY_SEPARATOR, $uri));
		}

		if (file_exists($file))
		{
			$resp = \Collei\Http\DataResponse::make(
				$request->mimeFromFileName($file)
			);

			$resp
				->setBody(file_get_contents($file))
				->output();

			exit(0);
		}
	}



}


