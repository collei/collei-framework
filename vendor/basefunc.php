<?php

define('PLAT_NAME', 'collei');
define('PLAT_ROOT', '');
define('PLAT_ROOT_URI', 'sites');
define('PLAT_FOLDER', basename(dirname(__FILE__, 2)));
define('PLAT_GROUND', dirname(__FILE__, 2));
define('DIR_SEP', DIRECTORY_SEPARATOR);

define('PLAT_LOGGING', [
	'classloader' => false,
]);

define('PLAT_APP_FOLDER_NAME', 'app');
define('PLAT_APP_GROUND', PLAT_GROUND . DIR_SEP . PLAT_APP_FOLDER_NAME);

define('PLAT_COMMANDS_FOLDER_NAME', 'commands');
define('PLAT_FILTERS_FOLDER_NAME', 'filters');
define('PLAT_MODELS_FOLDER_NAME', 'models');
define('PLAT_SERVICES_FOLDER_NAME', 'services');
define('PLAT_SERVLETS_FOLDER_NAME', 'servlets');

define('PLAT_APP_COMMANDS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_COMMANDS_FOLDER_NAME);
define('PLAT_APP_FILTERS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_FILTERS_FOLDER_NAME);
define('PLAT_APP_MODELS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_MODELS_FOLDER_NAME);
define('PLAT_APP_SERVICES_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_SERVICES_FOLDER_NAME);
define('PLAT_APP_SERVLETS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_SERVLETS_FOLDER_NAME);

define('PLAT_CONF_FOLDER_NAME', 'conf');
define('PLAT_CONF_GROUND', PLAT_GROUND . DIR_SEP . PLAT_CONF_FOLDER_NAME);

define('PLAT_VENDOR_FOLDER_NAME', 'vendor');
define('PLAT_VENDOR_GROUND', PLAT_GROUND . DIR_SEP . PLAT_VENDOR_FOLDER_NAME);

define('PLAT_SITES_FOLDER', 'sites');
define('PLAT_SITES_BASEURL', '/' . PLAT_SITES_FOLDER);
define('PLAT_SITES_GROUND', PLAT_GROUND . DIR_SEP . PLAT_SITES_FOLDER);

define('PLAT_RESOURCES_FOLDER_NAME', 'resources');
define('PLAT_RESOURCES_BASEURL', PLAT_SITES_BASEURL . '/' . PLAT_RESOURCES_FOLDER_NAME);
define('PLAT_RESOURCES_GROUND', PLAT_GROUND . DIR_SEP . PLAT_RESOURCES_FOLDER_NAME);

define('PLAT_VIEWS_FOLDER_NAME', 'views');
define('PLAT_RESOURCE_VIEWS_FOLDER', PLAT_RESOURCES_FOLDER_NAME . DIR_SEP . PLAT_VIEWS_FOLDER_NAME);
define('PLAT_RESOURCE_VIEWS_GROUND', PLAT_GROUND . DIR_SEP . PLAT_RESOURCE_VIEWS_FOLDER);

define('PLAT_SITES_CLASSES_ROOT_FOLDER', PLAT_APP_FOLDER_NAME);

define('PLAT_SITES_COMMANDS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_COMMANDS_FOLDER_NAME);
define('PLAT_SITES_FILTERS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_FILTERS_FOLDER_NAME);
define('PLAT_SITES_MODELS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_MODELS_FOLDER_NAME);
define('PLAT_SITES_SERVICES_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_SERVICES_FOLDER_NAME);
define('PLAT_SITES_SERVLETS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_SERVLETS_FOLDER_NAME);

define('PLAT_STORAGE_FOLDER_NAME', 'storage');
define('PLAT_STORAGE_GROUND', PLAT_GROUND . DIR_SEP . PLAT_STORAGE_FOLDER_NAME);

define('PLAT_LOGS_FOLDER_NAME', 'logs');
define('PLAT_LOGS_GROUND', PLAT_STORAGE_GROUND . DIR_SEP . PLAT_LOGS_FOLDER_NAME);

define('PLAT_CACHE_FOLDER_NAME', 'logs');
define('PLAT_CACHE_GROUND', PLAT_STORAGE_GROUND . DIR_SEP . PLAT_CACHE_FOLDER_NAME);

define('PLAT_STUB_FOLDER_NAME', 'stub');
define('PLAT_STUB_GROUND', PLAT_STORAGE_GROUND . DIR_SEP . PLAT_STUB_FOLDER_NAME);
define('PLAT_STUB_CATEGORY_CLASSES', 'classes');

define('PLAT_CLASSES_SUFFIX', '.php');
define('PLAT_VIEWS_SUFFIX', '.vis.php');
define('PLAT_STUB_SUFFIX', '.stub');

define('PLAT_BOOT_FILE', 'boot.php');
define('PLAT_ROUTE_FILE', 'route.php');
define('PLAT_DATABASE_FILE', 'database.php');
define('PLAT_FILTER_FILE', 'filter.php');
define('PLAT_PLUGIN_INIT_FILE', 'init.php');

/*
 *	Defines, if not yet, the standard input stream.
 */
if (!defined('STDIN'))
{
	define('STDIN', fopen('php://stdin', 'r'));
}

/*
 *	Defines, if not yet, the standard output stream.
 */
if (!defined('STDOUT'))
{
	define('STDOUT', fopen('php://stdout', 'w'));
}

/**
 *	Debug function: show contents and stops execution flow.
 *
 *	@param	mixed	$anything
 *	@return	string
 */
function dandie($anything)
{
	echo '<fieldset><pre>';
	var_dump($anything);
	echo '</pre></fieldset>';
	die();
}

/**
 *	Debug function: just show contents, without stopping execution flow.
 *	Using dandie() or logit() is recommended instead.
 *
 *	@param	mixed	$anything
 *	@return	string
 */
function donly($anything)
{
	echo '<fieldset><pre>';
	var_dump($anything);
	echo '</pre></fieldset>';
}

/**
 *	Debug function: brings contents as string
 *
 *	@param	mixed	$anything
 *	@return	string
 */
function sdumpy($anything)
{
	return print_r($anything, true);
}

/**
 *	Just a dummy function for internal use.
 *
 *	@param	mixed	...$anything
 *	@return	bool
 */
function dummyFunction_L395U4A(...$anything)
{
	return true;
}
//
define('DUMMY_FUNCTION_NAME', 'dummyFunction_L395U4A');

/**
 *	Encodes HTML tags for secure display to the user, using
 *	htmlspecialchars() with ENT_QUOTES and UTF-8.
 *
 *	@param	array	$info
 *	@return	void
 */
function html_to_display(...$anything)
{
	$text = $anything[0] ?? '';
	//
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 *	Initializes the log keepers.
 *
 *	@return	void
 */
function init_loggers()
{
	register_shutdown_function('finish_loggers');
}

/**
 *	Finishes the log keepers.
 *
 *	@return	void
 */
function finish_loggers()
{
	\Collei\App\Logger::save('plat');
}

/**
 *	Gathers the full path for the view source for a given site.
 *
 *	@param	mixed	$view_name
 *	@param	mixed	$site_name
 *	@param	mixed	&$site_from
 *	@return	mixed
 */
function plat_site_view_filename($view_name, $site_name, &$site_from)
{
	$view = (!is_null($view_name) ? trim($view_name) : '');
	$site_from = '';

	if (preg_match('/^\w+(\.\w+)*$/', $view) === false)
	{
		return false;
	}
	if (is_null($site_name) || empty($site_name))
	{
		$site_from = '';
		return plat_system_view_filename($view_name);
	}

	$view_platform = PLAT_RESOURCE_VIEWS_GROUND
		. DIR_SEP . str_replace('.', DIR_SEP, $view)
		. PLAT_VIEWS_SUFFIX;

	$view_site = PLAT_SITES_GROUND
		. DIR_SEP . $site_name
		. DIR_SEP . PLAT_RESOURCE_VIEWS_FOLDER
		. DIR_SEP . str_replace('.', DIR_SEP, $view)
		. PLAT_VIEWS_SUFFIX;

	if (file_exists($view_site))
	{
		$site_from = $site_name;
		return $view_site;
	}
	if (file_exists($view_platform))
	{
		return $view_platform;
	}
	return false;
}

/**
 *	Checks for presence of $class_name, returns false if it does not exist.
 *	For the sake of @inject in views, returns true if $class_name is empty
 *
 *	@param	string	$class_name = null
 *	@return	void
 */
function has_class(string $class_name = null)
{
	if (empty($class_name))
	{
		return true;
	}

	autold_logger("has_class() ", $class_name ?? '');
	require_manager_class($class_name);

	if ($site = \Collei\App\App::getInstance()->getSite())
	{
		autold_logger("has_class() ", $class_name ?? '');
		require_site_class($site, $class_name);
	}

	return class_exists($class_name, true);
}

/**
 *	Gathers the full path for the view source file of main platform site.
 *
 *	@param	mixed	$view_name
 *	@return	mixed
 */
function plat_system_view_filename($view_name)
{
	$view = (!is_null($view_name) ? trim($view_name) : '');
	if (preg_match('/^\w+(\.\w+)*$/', $view) === false)
	{
		return false;
	}

	$view_platform = PLAT_RESOURCE_VIEWS_GROUND
		. DIR_SEP . str_replace('.', DIR_SEP, $view)
		. PLAT_VIEWS_SUFFIX;

	if (file_exists($view_platform))
	{
		return $view_platform;
	}
	return false;
}

/**
 *	Loads site classes manually. For use of \Collei\App\App class.
 *
 *	@param	mixed	$site_name
 *	@param	mixed	$class_name
 *	@return	void
 */
function require_site_class($site_name, $class_name)
{
	$required = PLAT_SITES_GROUND . '/' . $site_name . '/' . $class_name
		. PLAT_CLASSES_SUFFIX;
	$required = preg_replace('#(\\/+|\\\\+)#', DIR_SEP, $required);
	//
	if (file_exists($required))
	{
		require_once $required;

		autold_logger("BYCALLER@$site_name: $class_name ",$required);

		return true;
	}
	//
	return false;
}

/**
 *	Loads Platform manager classes. For use of the \Collei\App\App class.
 *
 *	@param	mixed	$class_name
 *	@return	bool
 */
function require_manager_class($class_name)
{
	$required = PLAT_GROUND . '/' . $class_name . PLAT_CLASSES_SUFFIX;
	$required = preg_replace('#(\\/+|\\\\+)#', DIR_SEP, $required);
	//
	if (file_exists($required))
	{
		require_once $required;

		autold_logger("BYCALLER@PLAT: $class_name ",$required);

		return true;
	}
	return false;
}

/**
 *	Keeps plugin tracking for the system
 *
 *	@param	array	$info
 *	@return	void
 */
function plat_plugin_register(string $name, array $info)
{
	if (!isset($GLOBALS['__app.plugins']))
	{
		$GLOBALS['__app.plugins'] = array();
	}

	$GLOBALS['__app.plugins'][$name] = $info;

	return true;
}


/*
 *	keep site tracking
 */
function plat_site_register($site_name)
{
	if (!isset($GLOBALS['__app.sites']))
	{
		$GLOBALS['__app.sites'] = array();
	}

	$site_folder = PLAT_SITES_GROUND . DIR_SEP . $site_name;

	$GLOBALS['__app.sites'][$site_name] = array(
		'site' => $site_name,
		'source' => $site_folder . DIR_SEP . PLAT_SITES_CLASSES_ROOT_FOLDER,
	);
}


/*
 *	performs site registration
 */
function plat_site_scan()
{
	$site_dir = PLAT_SITES_GROUND;
	$site_dir_subfolders = array_diff(scandir($site_dir), array('..', '.'));

	foreach ($site_dir_subfolders as $sub_folder)
	{
		if (is_dir($site_dir . DIR_SEP . $sub_folder))
		{
			plat_site_register($sub_folder);
		}
	}
}


/*
 *	returns a list of loaded plugins
 */
function plat_plugin_list()
{
	if (isset($GLOBALS['__app.plugins']))
	{
		return array_keys($GLOBALS['__app.plugins']);
	}
	return null;
}


/*
 *	returns a list of loaded plugins and their info
 */
function plat_plugin_list_info()
{
	if (isset($GLOBALS['__app.plugins']))
	{
		return $list = $GLOBALS['__app.plugins'];
	}
	return null;
}


/*
 *	returns a list of existing sites
 */
function plat_site_list()
{
	if (isset($GLOBALS['__app.sites']))
	{
		return array_keys($GLOBALS['__app.sites']);
	}
	return null;
}


/*
 *	easy way of keeping temp config vars
 */
function genv($name)
{
	if (isset($GLOBALS['__app.debugging']))
	{
		if (isset($GLOBALS['__app.debugging'][$name]))
		{
			return $GLOBALS['__app.debugging'][$name];
		}
	}
	return null;
}

function senv($name, $value)
{
	if (!isset($GLOBALS['__app.debugging']))
	{
		$GLOBALS['__app.debugging'] = array();
	}
	$GLOBALS['__app.debugging'][$name] = $value;
	return true;
}

senv('debug-errors', true);
senv('debug-warnings', false);
senv('debug-notices', false);
senv('debug-raws', false);


/*
 *	easiful pattern matching for parameter extraction
 */
function fetch_uri_pattern($route_uri)
{
	$str_route = \Collei\Utils\Str::stripAfter($route_uri, '?');
	$outer_pattern = '[^\x2F]*';
	$pattern = '/^'.str_replace('/','\/',preg_replace('/\{[^}\/]*\}/m', $outer_pattern, $str_route)).'\/?$/';
	//
	return $pattern;
}

function fetch_uri_parameters($data_uri, $data_pattern)
{
	/*
	 *	pattern:	/cities/{city}/streets/{street}/homes/{home}
	 *	uri:		/cities/39/streets/27/homes/167
	 */
	$str_uri = $data_uri;
	$str_pattern = $data_pattern;
	$str_medium = preg_filter('/\{[^}\/]+\}/m', '*', $str_pattern);

	if (str_ends_with($str_uri, '/'))
	{
		$str_uri = substr($str_uri, 0, -1);
	}

	$arr_uri = explode('/', $str_uri);
	$arr_pattern = explode('/', $str_pattern);
	$arr_medium = explode('/', $str_medium);

	$len = count($arr_pattern);

	if (count($arr_uri) != $len || count($arr_medium) != $len)
	{
		return null;
	}

	$resulting_values = array();

	foreach ($arr_pattern as $i => $item_pattern)
	{
		$item_uri = $arr_uri[$i];
		$item_medium = $arr_medium[$i];

		if ($item_medium != '')
		{
			if ($item_pattern != $item_uri)
			{
				$name = substr($item_pattern, 1, -1);
				$resulting_values[$name] = trim($item_uri);
			}
		}
	}

	return $resulting_values;
}

/**
 *	List subfolders only, and excludes '.' and '..'
 *
 *	@param	string	$path	the folder to be scanned
 *	@return	array
 */
function dir_lis(string $path)
{
	$dirs = [];
	$items = scandir($path);

	foreach ($items as $item)
	{
		if (in_array($item, ['.','..']))
		{
			continue;
		}

		if (is_dir($path . DIR_SEP . $item))
		{
			$dirs[] = $item;
		}
	}

	return $dirs;
}


if (!function_exists('str_starts_with'))
{
	/**
	 *	Emulates php 8 function str_starts_with()
	 *
	 *	@param	string	$haystack
	 *	@param	string	$needle
	 *	@return	bool
	 */
	function str_starts_with(string $haystack, string $needle)
	{
		return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}
}

if (!function_exists('str_ends_with'))
{
	/**
	 *	Emulates php 8 function str_ends_with()
	 *
	 *	@param	string	$haystack
	 *	@param	string	$needle
	 *	@return	bool
	 */
	function str_ends_with(string $haystack, string $needle)
	{
		return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
	}
}

if (!function_exists('str_contains'))
{
	/**
	 *	Emulates php 8 function str_contains()
	 *
	 *	@param	string	$haystack
	 *	@param	string	$needle
	 *	@return	bool
	 */
	function str_contains(string $haystack, string $needle)
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}

if (!function_exists('array_key_last'))
{
	/**
	 *	Emulates php 7.3.0+ function array_key_last()
	 *
	 *	@param	array	$array
	 *	@return	int|string|null
	 */
	function array_key_last(array $array)
	{
		if (!is_array($array) || empty($array))
		{
			return NULL;
		}
		//
		return array_keys($array)[count($array)-1];
	}
}

if (!function_exists('mb_strlen'))
{
	/**
	 *	Replaces mb_strlen() if it is not found. 
	 *
	 *	@param	mixed	$str
	 *	@param	mixed	$encoding
	 *	@return	mixed
	 */
	function mb_strlen($str, $encoding = '8bit')
	{
		return \strlen($str);
	}
}

