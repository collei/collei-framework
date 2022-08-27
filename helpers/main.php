<?php

use Collei\App\App;
use Collei\Console\ConsoleApp;
use Collei\App\Logger;
use Collei\Http\Session;
use Collei\Views\View;
use Collei\Http\Routing\RouteResolver;
use Collei\Utils\Str;
use Collei\Utils\Validation\ValueChecker;
use Collei\Database\Yanfei\ModelResult;


if (!function_exists('redirect'))
{
	/**
	 *	Issues a site redirect
	 *	
	 *	@param	$destination	string	destination URI
	 *	@return	void
	 */
	function redirect(string $destination)
	{
		App::redirect($destination);
	}
}


if (!function_exists('resource'))
{
	/**
	 *	If the resource exists, returns its path
	 *	
	 *	@param	$resource		string	resource path
	 *	@return	string
	 */
	function resource(string $resource)
	{
		return App::getResourcePath($resource);
	}
}


if (!function_exists('app'))
{
	/**
	 *	Returns a new instance of the specified class,
	 *	or the instance of \Collei\App\App
	 *
	 *	@param	mixed	$virtual = null
	 *	@param	array	$params = []
	 *	@return	mixed
	 */
	function app($virtual = null, ...$params)
	{
		if (is_null($virtual))
		{
			return App::getInstance();
		}

		return App::getInstance()->make($virtual, $params);
	}
}


if (!function_exists('sess'))
{
	/**
	 *	Returns a instance of \Collei\Http\Session
	 *
	 *	@return	\Collei\Http\Session
	 */
	function sess()
	{
		return Session::capture();
	}
}


if (!function_exists('concat'))
{
	/**
	 *	Concatenates strings and other data into a single string
	 *
	 *	@param $piece		mixed	First chunk to concatenate
	 *	@param ...$pieces	mixed	Further chunks to concatenate
	 *	@return	string
	 */
	function concat($piece, ...$pieces)
	{
		return $piece . implode('', $pieces ?? ['']);
	}
}


if (!function_exists('route'))
{
	/**
	 *	Gets a instance of a Route
	 *	
	 *	@param $name			string	name or URI of a route
	 *	@param ...$arguments	mixed	value(s) to replace
	 *	@return			\Collei\Routing\Route
	 */
	function route($name, ...$arguments)
	{
		$route = RouteResolver::resolveByName($name);

		if (is_array($arguments))
		{
			return $route->assign(...$arguments)->getAssigned();
		}

		return $route;
	}
}


if (!function_exists('view'))
{
	/**
	 *	Gets a instance of a View 
	 *	
	 *	@param $name		string	name of the view
	 *	@param $variables	array	array with named keys to replace {things} {like} {these} in the view
	 *	@return			\Collei\Views\View
	 */
	function view($name, $variables = array())
	{
		$view = new View($name);
		$view->assignData($variables);
		return $view;
	}
}


if (!function_exists('logerror'))
{
	/**
	 *	Issues an error to the general log
	 *	
	 *	@param $error		string	a title or category
	 *	@param $description	string	brief description on the matter
	 *	@return			void
	 */
	function logerror($error, $description)
	{
		if (class_exists(Logger::class))
			Logger::log('error_log', $error, $description, Logger::PLAT_ERROR_ERROR);
		else
			autold_logger($error, $description, ':error_log');
	}
}


if (!function_exists('logwarn'))
{
	/**
	 *	Issues a warning to the general log
	 *	
	 *	@param $error		string	a title or category
	 *	@param $description	string	brief description on the matter
	 *	@return			void
	 */
	function logwarn($error, $description)
	{
		if (class_exists(Logger::class))
			Logger::log('warning_log', $error, $description, Logger::PLAT_ERROR_WARNING);
		else
			autold_logger($error, $description, ':warning_log');
	}
}


if (!function_exists('logit'))
{
	/**
	 *	Issues a notice to the general log
	 *	
	 *	@param $error		string	a title or category
	 *	@param $description	string	brief description on the matter
	 *	@return			void
	 */
	function logit($error, $description)
	{
		if (class_exists(Logger::class))
			Logger::log('notice_log', $error, $description, Logger::PLAT_ERROR_NOTICE);
		else
			autold_logger($error, $description, ':notice_log');
	}
}


if (!function_exists('asset'))
{
	/**
	 *	For views: generates the proper tag for including CSS or JS
	 *	
	 *	@param	$asset_file	string	relative path to the resources
	 *	@return	string
	 */
	function asset($asset_file)
	{
		$DS = DIRECTORY_SEPARATOR;
		$site = app()->getSite() ?? PLAT_NAME;
		$real = str_replace('/', $DS, $asset_file);
		$files = [];

		if ($site != PLAT_NAME)
		{
			$files[$site] = [
				'path' => PLAT_SITES_GROUND . "{$DS}{$site}{$DS}"
					. PLAT_RESOURCES_FOLDER_NAME . "{$DS}{$real}",
				'uri' => PLAT_SITES_BASEURL . "/{$site}/"
					. PLAT_RESOURCES_FOLDER_NAME . "/{$asset_file}", 
			];
		}

		$files[PLAT_NAME] = [
			'path' => PLAT_RESOURCES_GROUND . "{$DS}{$real}",
			'uri' => PLAT_RESOURCES_BASEURL . "/{$asset_file}", 
		];

		foreach ($files as $file)
		{
			if (file_exists($file['path']))
			{
				return $file['uri'];
			}
		}

		return '/';
	}
}


if (!function_exists('toString'))
{
	/**
	 *	converts data to string
	 *	
	 *	@param $anything	mixed	data to be cast into
	 *	@return			string
	 */
	function toString($anything)
	{
		if (is_object($anything))
		{
			if (ValueChecker::objectIsStringable($anything))
			{
				return (string)$anything;
			}
			return '';
		}
		return (string)$anything;
	}
}


if (!function_exists('toJson'))
{
	/**
	 *	converts data to Json format
	 *	
	 *	@param $anything	mixed	data to be encoded
	 *	@return			string	the JSON result
	 */
	function toJson($anything)
	{
		return json_encode($anything);
	}
}


if (!function_exists('ilink'))
{
	/**
	 *	creates links for parts of the current site
	 *	(does not check for user permissions)
	 *	
	 *	@param $label	string	the link label
	 *	@param $url		string	the link url
	 *	@return			string	the html <a> link
	 */
	function ilink($label, $url)
	{
		return '<a href="' . $url . '">' . $label . '</a>';
	}
}


if (!function_exists('olink'))
{
	/**
	 *	creates links for outside
	 *	
	 *	@param $label	string	the link label
	 *	@param $url		string	the link url
	 *	@return			string	the html <a> link
	 */
	function olink($label, $url)
	{
		return '<a href="' . $url . '" target="_blank">' . $label . '</a>';
	}
}


if (!function_exists('slink'))
{
	/**
	 *	creates links for parts of another sites
	 *	(does not check for user permissions) 
	 *	
	 *	@param $label	string	the link label
	 *	@param $url		string	the link url
	 *	@return			string	the html <a> link
	 */
	function slink($label, $site_url)
	{
		return '<a href="' . $site_url . '">' . $label . '</a>';
	}
}


if (!function_exists('ialink'))
{
	/**
	 *	creates links for parts of the current site
	 *	(checks for user permissions) 
	 *	
	 *	@param $label	string	the link label
	 *	@param $url		string	the link url
	 *	@return			string	the html <a> link
	 */
	function ialink($label, $url)
	{
		return '<a href="' . $url . '">' . $label . '</a>';
	}
}


if (!function_exists('salink'))
{
	/**
	 *	creates links for parts of another sites (checks for user permissions) 
	 *	
	 *	@param $label	string	the link label
	 *	@param $url		string	the link url
	 *	@return			string	the html <a> link
	 */
	function salink($label, $site_url)
	{
		return '<a href="' . $site_url . '">' . $label . '</a>';
	}
}


if (!function_exists('auth'))
{
	/**
	 *	checks for user authentication
	 *	
	 *	@param $guard	string	the auth guard to use
	 *	@return			bool	true if user is logged, false otherwise
	 */
	function auth($guard = null)
	{
		$sess = Session::capture();

		if (!isset($sess->user))
		{
			return false;
		}

		if (!is_null($guard) && ($guard != ''))
		{
			return $sess->user->hasRole($guard, site());
		}

		return true;
	}
}


if (!function_exists('guest'))
{
	/**
	 *	checks for user authentication
	 *	
	 *	@return			bool	true if user is NOT logged, false otherwise
	 */
	function guest()
	{
		$sess = Session::capture();

		return !isset($sess->user);
	}
}


if (!function_exists('csrf'))
{
	/**
	 *	Returns the CSRF token to be issued at next form post submit 
	 *	
	 *	@return			string	the generated token
	 */
	function csrf()
	{
		$csrf_token = Session::capture()->csrf;
		return $csrf_token;
	}
}


if (!function_exists('site'))
{
	/**
	 *	Returns the site name the current script belongs to 
	 *	
	 *	@return	string
	 */
	function site()
	{
		return App::getInstance()->getSite();
	}
}


if (!function_exists('printif'))
{
	/**
	 *	Returns the second argument if the first is true,
	 *	or empty string if false 
	 *	
	 *	@return	string
	 */
	function printif($test, $value)
	{
		if ($test)
		{
			return $value;
		}
		return '';
	}
}


if (!function_exists('iif'))
{
	/**
	 *	Returns the second argument if the first is true,
	 *	or the third one if false 
	 *	
	 *	@param	bool	$test
	 *	@param	string	$ifTrue
	 *	@return	string	$ifFalse
	 */
	function iif($test, $ifTrue, $ifFalse)
	{
		if ($test)
		{
			return $ifTrue;
		}
		return $ifFalse;
	}
}


if (!function_exists('coalesce'))
{
	/**
	 *	Returns the first argument if it is not empty,
	 *	the second one otherwise 
	 *	
	 *	@param	mixed	$thing
	 *	@param	mixed	$alternative
	 *	@return	mixed
	 */
	function coalesce($thing, $alternative)
	{
		if (!empty($thing))
		{
			return $thing ?? $alternative;
		}
		return $alternative;
	}
}


if (!function_exists('root'))
{
	/**
	 *	Returns the ground (OS path) of the plataform,
	 *
	 *	@return	string
	 */
	function root()
	{
		return PLAT_GROUND;
	}
}


if (!function_exists('ground'))
{
	/**
	 *	Returns the ground (OS path) of the current site,
	 *
	 *	@return	string
	 */
	function ground()
	{
		return App::getInstance()->getGround();
	}
}


if (!function_exists('groundOf'))
{
	/**
	 *	Returns the ground (OS path) of the given $site,
	 *
	 *	@param	string	$site	name of an existing site
	 *	@return	string	the resouce accesible path
	 */
	function groundOf(string $site)
	{
		if ($info = App::siteInfo($site))
		{
			return $info['ground'];
		}
		//
		if ($info = ConsoleApp::siteInfo($site))
		{
			return $info['ground'];
		}
		//
		return '';
	}
}


if (!function_exists('grounded'))
{
	/**
	 *	Returns the full path of the given filename (OS path), if any
	 *
	 *	@param	string	$fileName
	 *	@return	string
	 */
	function grounded(string $fileName)
	{
		$bt = debug_backtrace();

		foreach ($bt as $item)
		{
			$file = dirname($item['file']) . DIRECTORY_SEPARATOR . $fileName;

			if (file_exists($file))
			{
				return $file;
			}
		}

		return '';
	}
}


if (!function_exists('rootGrounded'))
{
	/**
	 *	Returns the full OS path of the filename, given its path
	 *	from the PLAT_GROUND basedir
	 *
	 *	@param	string	$fileName
	 *	@return	string
	 */
	function rootGrounded(string $fileName)
	{
		return PLAT_GROUND . DIRECTORY_SEPARATOR . $fileName;
	}
}


if (!function_exists('resourceGround'))
{
	/**
	 *	Returns the ground of the resources folder for the current site,
	 *	or the absolute path for the folder/file named $resource  
	 *
	 *	@param	string	$resource	name of a folder and/or file
	 *	@return	string	the absolute path
	 *		(e.g. windows:	C:\<your-web-server>\www\plat\sites\megaloja\resources	)
	 *		(e.g. unix:		/var/www/plat/sites/megaloja/resources 				)
	 */
	function resourceGround($resource)
	{
		$ground = ground() . DIRECTORY_SEPARATOR . PLAT_RESOURCES_FOLDER_NAME;

		$target = $ground
			. DIRECTORY_SEPARATOR
			. preg_replace('#(\\/+|\\\\+)#', DIRECTORY_SEPARATOR, $resource);

		if (file_exists($target) || is_dir($target))
		{
			return $target;
		}

		return $ground;
	}
}


if (!function_exists('resource'))
{
	/**
	 *	Returns the base URI of the resources folder for the current site,
	 *	or the absolute URI for the folder/file named $resource  
	 *
	 *	@param	string	$resource	name of a folder and/or file
	 *	@return	string	the resouce accesible path (e.g. /sites/megaloja/resources)
	 */
	function resource($resource)
	{
		$rootURI = App::getInstance()->getRootURI() . '/' . PLAT_RESOURCES_FOLDER;

		return $rootURI . '/' . $resource;
	}
}


if (!function_exists('collision'))
{
	/**
	 *	Returns the common string that is both the suffix of $first
	 *	and the prefix of $rear,
	 *
	 *	@return	string	the collision
	 */
	function collision($front, $rear)
	{
		return Str::collision((string)$front, (string)$rear);
	}
}


if (!function_exists('collided'))
{
	/**
	 *	Ask if the common string (that is both the suffix of
	 *	$first and the prefix of $rear) exists,
	 *
	 *	@return	bool	true if a collision exists, false otherwise
	 */
	function collided($front, $rear)
	{
		return Str::collided((string)$front, (string)$rear);
	}
}


if (!function_exists('collapse'))
{
	/**
	 *	Joins two strings ignoring the collision, if any
	 *
	 *	@return	string	the joint string
	 */
	function collapse($front, $rear)
	{
		return Str::collapse((string)$front, (string)$rear);
	}
}

if (!function_exists('isEmpty'))
{
	/**
	 *	Tells if $anything is an empty value -- even though it is
	 *	a ModelResult instance!
	 *
	 *	@param	mixed	$anything
	 *	@return	bool
	 */
	function isEmpty($anything)
	{
		if ($anything instanceof ModelResult)
		{
			return $anything->empty();
		}
		return empty($anything);
	}
}


if (!function_exists('dictionary_divisor'))
{
	/**
	 *	@var string $dictionary_divisor
	 */
	$GLOBALS['dictionary_divisor'] = '';

	/**
	 *	Builds a dictionary listing divisor
	 *
	 *	@param	mixed	$list
	 *	@param	mixed	$item
	 *	@return	bool
	 */
	function dictionary_divisor($list, $item)
	{
		$prior = $list->previous();
		$letra_anterior = '';

		if (!is_null($prior))
		{
			$letra_anterior = strtoupper(
				substr(Str::cleanDiacritics($prior->entry),0,1)
			);
		}

		$letra_atual = strtoupper(
			substr(Str::cleanDiacritics($item->entry),0,1)
		);

		if ($letra_atual != $letra_anterior)
		{
			$GLOBALS['dictionary_divisor'] = $letra_atual;
			return true;
		}

		return false;
	}

	/**
	 *	Returns the current dictionary listing divisor
	 *
	 *	@return	string
	 */
	function dictionary_divisor_letter()
	{
		return $GLOBALS['dictionary_divisor'];
	}
}


