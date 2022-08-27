<?php 
namespace Collei\App;

use Collei\Utils\Files\TextFile;
use Collei\Utils\Files\ConfigFile;
use Collei\Exceptions\ColleiException;
use Collei\Http\Routing\Route;
use Collei\Http\Routing\RouteResolver;
use Collei\Http\ServletDispatcher;
use Collei\Http\Request;
use Collei\Http\Response;
use Collei\Http\Session;
use Collei\Http\Filters\Filter;
use Collei\Http\Filters\FilterChain;
use Collei\Views\View;
use Collei\App\AppURL;
use Collei\App\Logger;
use Collei\App\Loaders\ClassLoader;
use Collei\Utils\Str;
use Exception;
use Throwable;

/**
 *	Encapsulates the running application instance
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class App
{
	/** 
	 *	@var \Collei\Http\Request $request
	 */
	private $request;

	/**
	 *	@var \Collei\Http\Response $response
	 */
	private $response;

	/**
	 *	@var \Collei\Http\Session $session
	 */
	private $session;

	/**
	 *	@var \Collei\Utils\Files\ConfigFile $conf
	 */
	private $conf;

	/**
	 *	@var \Collei\App\AppURL $url
	 */
	private $url;

	/**
	 *	@var bool $can_run - true if it has a route assigned, false otherwise
	 */
	private $can_run = false;

	/**
	 *	@var string $app_site
	 */
	private $app_site = '';

	/**
	 *	@var \Collei\Routing\Route $app_current_route
	 */
	private $app_current_route = null;

	/**
	 *	@var string $hidden_output - collected hidden outputs
	 */
	private $hidden_output = '';

	/**
	 *	@return void
	 */
	private function loadConfigs()
	{
		$file = PLAT_CONF_GROUND . DIRECTORY_SEPARATOR . '.app';
		//
		$this->conf = new ConfigFile($file);
	}

	/**
	 *	@return void
	 */
	private function gatherHiddenOutput()
	{
		$this->hidden_output = @ob_get_contents();
		//
		@ob_end_clean();
	}

	/**
	 *	@return void
	 */
	private function outputResponse()
	{
		$this->gatherHiddenOutput();
		//
		@ob_start();
		$this->response->output();
		@ob_end_flush();
	}

	/**
	 *	@return array
	 */
	private function getPlatformInfo()
	{
		$sites = dir_lis(PLAT_SITES_GROUND);
		$isBasefold = ($this->getConfigParam('app.basefolderInUrl', 0) == 1);
		$info = [];
		//
		foreach ($sites as $site)
		{
			$piece = [
				'name' => $site,
				'url' => PLAT_SITES_BASEURL . '/' . $site,
				'ground' => PLAT_SITES_GROUND . DIRECTORY_SEPARATOR . $site,
			];
			//
			if ($isBasefold)
			{
				$piece['url'] = PLAT_FOLDER . '/' . $piece['url'];
			}
			//
			$info[$site] = $piece;
		}
		//
		return $info;
	}

	/**
	 *	@return void
	 */
	private function initializePlatform()
	{
		$sectors = [
			PLAT_BOOT_FILE,
			PLAT_ROUTE_FILE,
			PLAT_FILTER_FILE,
			PLAT_DATABASE_FILE
		];
		//
		foreach ($sectors as $sector)
		{
			$sector_file = PLAT_APP_GROUND . DIRECTORY_SEPARATOR . $sector;
			//
			if (file_exists($sector_file))
			{
				require_once $sector_file;
			}
		}
		//
		static::$siteStructs = $this->getPlatformInfo();
	}

	/**
	 *	@return void
	 */
	private function initializeSite()
	{
		$sectors = [
			PLAT_BOOT_FILE,
			PLAT_ROUTE_FILE,
			PLAT_FILTER_FILE,
			PLAT_DATABASE_FILE
		];
		//
		$site_ground = $this->url->getGround();
		//
		foreach ($sectors as $sector)
		{
			$sector_file = $site_ground . DIRECTORY_SEPARATOR . $sector;
	
			if (file_exists($sector_file))
			{
				require_once $sector_file;
			}
		}
	}

	/**
	 *	@return void
	 */
	private function assignRoute()
	{
		$route = $this->getRouteFound();
		$site_name = $this->url->getFolder();
		//
		$a_dir = PLAT_SITES_GROUND . DIRECTORY_SEPARATOR . $site_name;
		//
		if (is_dir($a_dir))
		{
			$this->app_site = $site_name;
		}
		else
		{
			$this->app_site = PLAT_NAME;
		}
		//
		$this->request->bindRoute($route);
		$this->can_run = true;
	}

	/**
	 *	@return void
	 */
	private function initialize(
		Request $request, Response $response, Session $session
	) {
		self::$currentInstance = $this;
		//
		$this->loadConfigs();
		//
		$this->request = $request;
		$this->response = $response;
		$this->session = $session;
		$this->url = new AppURL($this->request->uri);
		//
		$this->initializePlatform();
		$this->initializeSite();
		//
		if ($this->hasRouteFor($this->url->path)) {
			$this->assignRoute();
		}

		logit('NO_ROUTE', "No route\r\n\t\tfor {$this->request->uri}");
	}

	/**
	 *	Returns whether the view exists
	 *
	 *	@param	string	$viewName	name of a view
	 *	@return bool 	true if it exists, false otherwise
	 */
	private function hasView(string $viewName)
	{
		$this->app_current_route = RouteResolver::resolve($uri);
		return !is_null($this->app_current_route);
	}

	/**
	 *	Returns if is there a route for this request uri
	 *
	 *	@param	string	$uri	the request uri
	 *	@return bool 	true if it exists, false otherwise
	 */
	private function hasRouteFor(string $uri)
	{
		$this->app_current_route = RouteResolver::resolve(
			$uri, $this->request->method
		);
		//
		return !is_null($this->app_current_route);
	}

	/**
	 *	Returns the found route for the application instance
	 *
	 *	@return \Collei\Routing\Route	the matched route
	 */
	private function getRouteFound()
	{
		return $this->app_current_route;
	}

	/**
	 *	Process the given View instance into a response
	 *
	 *	@param	\Collei\Views\View	$view	the view to be processed	
	 *	@return \Collei\Http\Response	the response
	 */
	private function processView(View $view)
	{
		$body = $view->fetch();
		$response = Response::make();
		$response->setBody($body);
		//
		return $response;
	}

	/**
	 *	Fires a call to the corresponding servlet to fetch the response from
	 *
	 *	@param	\Collei\Routing\ServletDispatcher	$dispatcher
	 *	@return \Collei\Http\Response	the response
	 */
	private function callDispatcher(ServletDispatcher $dispatcher)
	{
		$returned = $dispatcher->dispatch(
			$this->app_current_route,
			$this->request
		);
		//
		if (empty($returned))
		{
			return Response::make();
		}
		//
		if (is_string($returned))
		{
			$response = Response::make();
			$response->setBody($returned);

			return $response;
		}
		//
		if ($returned instanceof View)
		{
			return $this->processView($returned);
		}
		//
		return $returned;
	}

	/**
	 *	Fires a call to the corresponding servlet through a dispatcher
	 *
	 *	@return \Collei\Http\Response
	 */
	private function runServlet()
	{
		if ($this->can_run) {
			$this->response = $this->callDispatcher(
				new ServletDispatcher($this)
			);
		} else {
			$this->response = new Response();
		}
	}

	/**
	 *	Fires a call to the filter chain. Returns the failed filter
	 *	through the parameter, or null on success
	 *
	 *	@param	null|\Collei\Http\Filters\Filter	&$failed
	 *	@return mixed
	 */
	private function callFilterChain(Filter &$failed = null)
	{
		$failed = null;
		//
		return FilterChain::run($this->request, Response::make(), $failed);
	}

	/**
	 *	Start the filter chain
	 *
	 *	@return bool
	 */
	private function runFilters()
	{
		$filter = null;
		//
		if ($this->can_run)
		{
			$returned = $this->callFilterChain($filter);
			//
			if ($returned !== true)
			{
				if ($returned instanceof View)
				{
					$this->response = $this->processView($returned);
				}
				elseif ($returned instanceof Response)
				{
					$this->response = $returned;
				}
				else
				{
					$this->response = new Response();
				}
				//
				return false;
			}
			//
			return true;
		}
		else
		{
			$this->response = Response::make();
			//
			$this->response->setBody(
				'There is no corresponding Route to the request '
					. 'at the follwowing URI: ' . $this->url . '. ');
			return false;
		}
	}

	/**
	 *	Elaborates proper response output in case of some Exception
	 *
	 *	@param	mixed	$exception
	 *	@return	void
	 */
	private function prepareErrorResponse($exception)
	{
		$title = ($exception instanceof ColleiException)
			? ($exception->getTitle() ?? get_class($exception))
			: get_class($exception);
		//
		$message = $exception->getMessage();
		$trace = $exception->getTraceAsString();
		//
		logerror(get_class($exception), $message . "\r\n" . $trace);
		//
		$this->response = $this->processView(view(
			'error.generic',
			[
				'title' => $title,
				'longText' => $message,
				'trace' => $trace
			]
		));
	}

	/**
	 *	Fires app instance initialization
	 *
	 *	@param	\Collei\Http\Request	$request
	 */
	private function __construct(Request $request)
	{
		$this->initialize(
			$request,
			Response::make(),
			Session::capture()
		);
	}

	/**
	 *	Finalizes the instance
	 *
	 *	@return void
	 */
	public final function __destruct()
	{
		Logger::save();
	}

	/**
	 *	Start the application instance
	 *
	 *	@return void
	 */
	public function run()
	{
		try {
			if ($this->runFilters()) {
				$this->runServlet();
			}
			//
			$this->response->setCookie('vis', date('Y-m-d H:i:s'));
		} catch (Throwable $t) {
			$this->prepareErrorResponse($t);
		} catch (Exception $ex) {
			$this->prepareErrorResponse($ex);
		}
		//
		$this->outputResponse();
	}

	/**
	 *	Returns the request object
	 *
	 *	@return \Collei\Http\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 *	Returns the response object
	 *
	 *	@return \Collei\Http\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 *	Returns the current session
	 *
	 *	@return \Collei\Http\Session
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 *	Returns the value of the specified parameter. 
	 *	$alternative is returned when $param is not found.
	 *
	 *	@param	string	$param	the param name	
	 *	@param	string	$alternative 	
	 *	@return string
	 */
	public function getConfigParam(string $param, string $alternative = null)
	{
		return $this->conf->get($param, $alternative);
	}

	/**
	 *	Returns the root URI of the current application
	 *
	 *	@return string
	 */
	public function getRootURI()
	{
		return $this->url->getRoot();
	}

	/**
	 *	Returns the current route
	 *
	 *	@return \Collei\Routing\Route
	 */
	public function getRoute()
	{
		return $this->app_current_route;
	}

	/**
	 *	Returns the name of the application
	 *
	 *	@return string
	 */
	public function getSite()
	{
		return $this->app_site;
	}

	/**
	 *	Returns the root path of the application in the underlying
	 *	OS format (e.g., a Windows path)
	 *
	 *	@return string
	 */
	public function getGround()
	{
		return $this->url->getGround();
	}

	/**
	 *	Returns the root path of the given $site in the underlying
	 *	OS format (e.g., a Windows path)
	 *
	 *	@return string
	 */
	public function getGroundOf(string $site)
	{
		return $this->url->getGround();
	}

	/**
	 *	Returns the root path of the application storage in the
	 *	underlying OS format (e.g., a Windows path)
	 *
	 *	@return string
	 */
	public function getStorageGround()
	{
		if ($this->app_site != PLAT_NAME)
		{
			return $this->url->getGround()
				. DIRECTORY_SEPARATOR
				. PLAT_STORAGE_FOLDER_NAME;
		}
		//
		return PLAT_STORAGE_GROUND;
	}

	/**
	 *	Returns the resource URI if it exists, or the first found of the
	 *	alternatives, or false
	 *
	 *	@param	string	$resource
	 *	@param	string	...$alternatives
	 *	@return	string|bool
	 */
	public function getSiteResource(string $resource, string ...$alternatives)
	{
		$ds = DIRECTORY_SEPARATOR;
		$site = $this->getSite();
		$folders = [];
		//
		$possible = [
			[
				'/' . PLAT_FOLDER . '/' . PLAT_SITES_BASEURL . '/' . $site
					. '/' . PLAT_RESOURCES_FOLDER_NAME . '/' . $resource,
				PLAT_SITES_GROUND . DIRECTORY_SEPARATOR . $site
					. DIRECTORY_SEPARATOR . PLAT_RESOURCES_FOLDER_NAME
					. DIRECTORY_SEPARATOR
					. str_replace('/', DIRECTORY_SEPARATOR, $resource),
			],
			[
				'/' . PLAT_FOLDER . '/' . PLAT_RESOURCES_BASEURL
					. '/' . $resource,
				PLAT_RESOURCES_GROUND . DIRECTORY_SEPARATOR
					. str_replace(['///','//','/'], DIRECTORY_SEPARATOR, $resource),
			]
		];
		//
		foreach ($alternatives as $alternative)
		{
			$possible[] = [
				'/' . PLAT_FOLDER . '/' . PLAT_SITES_BASEURL . '/' . $site
					. '/' . PLAT_RESOURCES_FOLDER_NAME . '/' . $alternative,
				PLAT_SITES_GROUND . DIRECTORY_SEPARATOR . $site
					. DIRECTORY_SEPARATOR . PLAT_RESOURCES_FOLDER_NAME
					. DIRECTORY_SEPARATOR
					. str_replace(['///','//','/'], DIRECTORY_SEPARATOR, $alternative),
			];
			$possible[] = [
				'/' . PLAT_FOLDER . '/' . PLAT_RESOURCES_BASEURL
					. '/' . $alternative,
				PLAT_RESOURCES_GROUND . DIRECTORY_SEPARATOR
					. str_replace(['///','//','/'], DIRECTORY_SEPARATOR, $alternative),
			];
		}
		//
		foreach ($possible as $poss)
		{
			if (file_exists($poss[1]))
			{
				return str_replace(['///','//','/'], '/', $poss[0]);
			}
		}
		//
		return false;
	}

	/**
	 *	Loads any class with the given parameters
	 *
	 *	@param	mixed	$virtual = null
	 *	@param	array	$params = []
	 *	@return	instanceof @virtual|\Collei\App\App
	 */
	public function make($virtual = null, array $params = [])
	{
		return ClassLoader::load($virtual, $params);
	}

	/**
	 *	Returns a version of the $path with the root folder appended,
	 *	if configured according to /plat/conf/.app directives
	 *
	 *	@return string
	 */
	public function getRootFolderAppended(string $path)
	{
		$basefold = $this->getConfigParam('app.basefolder', '/');
		$isBasefold = ($this->getConfigParam('app.basefolderInUrl', 0) == 1);
		//
		if ($isBasefold)
		{
			if (($basefold != '') && ($basefold != '/'))
			{
				if (!Str::startsWith($path, "/$basefold"))
				{
					return '/' . $basefold . $path;
				}
			}
		}
		//
		return $path;
	}

	/**
	 *	@var \Collei\App\App $currentInstance
	 */
	private static $currentInstance = null;

	/**
	 *	@var array $siteStructs
	 */
	private static $siteStructs = [];

	/**
	 *	Returns basic info on the give site
	 *
	 *	@param	string	$site	site shortname (site folder name)
	 *	@return	array|null
	 */
	public static function siteInfo(string $site)
	{
		return static::$siteStructs[$site] ?? null;
	}

	/**
	 *	Returns a list of existing sites in the path
	 *
	 *	@return	array|null
	 */
	public static function sites()
	{
		return Arr::keys(static::$siteStructs);
	}

	/**
	 *	Start the application with the captured request
	 *
	 *	@param	\Collei\Http\Request	the captured request
	 *	@return \Collei\App\App
	 */
	public static function start(Request $request)
	{
		if (is_null(self::$currentInstance))
		{
			self::$currentInstance = new static($request);
		}
		//
		return self::$currentInstance;
	}

	/**
	 *	Returns the current application instance
	 *
	 *	@return \Collei\App\App
	 */
	public static function getInstance()
	{
		return self::$currentInstance;
	}

	/**
	 *	Returns the resource URI if it exists, or the first found of the
	 *	alternatives, or false
	 *
	 *	@param	string	$resource
	 *	@param	string	...$alternatives
	 *	@return	string|bool
	 */
	public static function getResourcePath(
		string $resource, string ...$alternatives
	)
	{
		return self::getInstance()->getSiteResource(
			$resource, ...$alternatives
		);
	}

	/**
	 *	Issues a redirect
	 *
	 *	@return void
	 */
	public static function redirect($to)
	{
		$instance = self::getInstance();
		$site = $instance->getSite();
		$destination = $to;
		//
		$resolvedA = RouteResolver::resolve($to, 'GET');
		/*
		 *	For a redirect issued by a site, recalculates it and
		 *	generates the due complete URI, so the desired target
		 *	is achieved 
		 */
		if ($site != '' && $site != PLAT_NAME)
		{
			$plat_base = PLAT_SITES_BASEURL;
			$site_base = PLAT_SITES_BASEURL . '/' . $site;

			if (!Str::startsWith($to, $plat_base) && !Str::startsWith($to, $site_base))
			{
				$to = PLAT_SITES_BASEURL . '/' . $site . $destination;
			}
		}
		else
		{
			if (!Str::startsWith($to, PLAT_SITES_BASEURL))
			{
				$to = PLAT_SITES_BASEURL . $destination;
			}
		}
		//
		$resolvedB = RouteResolver::resolve($to, 'GET');
		/*
		 *	Let's first try resolving $to within a GET route (if it exists), 
		 *	with itself as a fallback 
		 */
		$destination = $resolvedB;
		//
		if (empty($destination))
		{
			$destination = $to;
		}
		elseif ($destination->isVariable)
		{
			$destination = $to;
		}
		//
		$destination = $instance->getRootFolderAppended($destination);
		//
		//	Cleans up all the output before doing it
		$instance->gatherHiddenOutput();
		//
		//	Performs the reidrect
		$instance->response->redirect('' . $destination . '');
	}

}

