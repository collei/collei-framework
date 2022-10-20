<?php

define('PLAT_NAME', 'collei');
define('PLAT_ROOT', '');
define('PLAT_ROOT_URI', 'sites');
define('PLAT_FOLDER', basename(dirname(__DIR__)));
define('PLAT_GROUND', dirname(__DIR__));
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
define('PLAT_LISTENERS_FOLDER_NAME', 'listeners');
define('PLAT_EVENTS_FOLDER_NAME', 'events');

define('PLAT_APP_SPECIAL_FOLDER_NAMES', array(
	PLAT_MODELS_FOLDER_NAME,
	PLAT_SERVLETS_FOLDER_NAME,
	PLAT_SERVICES_FOLDER_NAME,
	PLAT_COMMANDS_FOLDER_NAME,
	PLAT_FILTERS_FOLDER_NAME,
	PLAT_EVENTS_FOLDER_NAME,
	PLAT_LISTENERS_FOLDER_NAME
));

define('PLAT_APP_COMMANDS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_COMMANDS_FOLDER_NAME);
define('PLAT_APP_FILTERS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_FILTERS_FOLDER_NAME);
define('PLAT_APP_MODELS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_MODELS_FOLDER_NAME);
define('PLAT_APP_SERVICES_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_SERVICES_FOLDER_NAME);
define('PLAT_APP_SERVLETS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_SERVLETS_FOLDER_NAME);
define('PLAT_APP_LISTENERS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_LISTENERS_FOLDER_NAME);
define('PLAT_APP_EVENTS_FOLDER', PLAT_APP_FOLDER_NAME . DIR_SEP . PLAT_EVENTS_FOLDER_NAME);

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
define('PLAT_SITES_LISTENERS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_LISTENERS_FOLDER_NAME);
define('PLAT_SITES_EVENTS_FOLDER', PLAT_SITES_CLASSES_ROOT_FOLDER . DIR_SEP . PLAT_EVENTS_FOLDER_NAME);

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

require_once PLAT_GROUND . '/helpers/main.php';

/*
 *	Defines, if not yet, the standard input stream.
 */
if (!defined('STDIN')) {
	define('STDIN', fopen('php://stdin', 'r'));
}

/*
 *	Defines, if not yet, the standard output stream.
 */
if (!defined('STDOUT')) {
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
 *	Helps to identify which functions/methods are really inutile.
 *	Add right at first line of function:
 *		return log_unused()
 *	You can pass the return you  usuallty wait for a ginve function.
 *	E.g.: write...   		if your function must return...
 *		log_unused(true)		bool(true)
 *		log_unused(0)			int
 *		log_unused(0.0)			float
 *		log_unused([])			array
 *		log_unused('')			string
 *		log_unused(null)		null
 *		log_unused()			void (no return value)
 *
 *	@param	mixed	...$returns
 *	@return	mixed
 */
function log_unused(...$returns)
{
	$dbt = debug_backtrace();
	//
	$caller = [
		$dbt[2]['function'] ?? '[NOFUNC]',
		$dbt[2]['line'] ?? 0,
		$dbt[2]['file'] ?? '[NOFILE]',
	];
	$callee = [
		$dbt[1]['function'] ?? '[NOFUNC]',
		$dbt[1]['line'] ?? 0,
		$dbt[1]['file'] ?? '[NOFILE]',
	];
	$log = "\r\n\t\t{$callee[0]}({$callee[1]}) at {$callee[2]}"
		. "\r\n\t\t\tcalled by {$caller[0]}({$caller[1]}) at {$caller[2]}\r\n";
	logit(__FUNCTION__, "[UNUSED_NOTICE] $log");
	//
	if (count($returns) == 0) {
		return;
	}
	//
	return $returns[0] ?? null;
}

/**
 *	Returns a list of registered sites
 *
 *	@return	array
 */
function plat_site_list()
{
	return \Collei\App\Environment::listSites();
}

/**
 *	Returns a list of registered sites with their info
 *
 *	@return	array
 */
function plat_site_list_info()
{
	return \Collei\App\Environment::listSitesInfo();
}

/**
 *	Gets value from a variable in the App environment
 *
 *	@param	string	$name
 *	@return	mixed|null
 */
function genv($name)
{
	return \Collei\App\Environment::getAppEnv($name);
}

/**
 *	Sets a value to a variable in the App environment
 *
 *	@param	string	$name
 *	@param	mixed	$value
 *	@return	void
 */
function senv($name, $value)
{
	\Collei\App\Environment::setAppEnv($name, $value);
}

/**
 *	Sets one (or more) value(s) to variable(s) in the App environment
 *
 *	@param	array	$namedValues
 *	@return	void
 */
function senvs(array $namedValues)
{
	foreach ($namedValues as $name => $value) {
		\Collei\App\Environment::setAppEnv($name, $value);
	}
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
	//
	foreach ($items as $item) {
		if (in_array($item, ['.','..'])) {
			continue;
		}
		//
		if (is_dir($path . DIR_SEP . $item)) {
			$dirs[] = $item;
		}
	}
	//
	return $dirs;
}

/*----------------------------------------------------------*
 *	Function wrappers for support on earlier PHP versions	*
 *----------------------------------------------------------*/

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
		return (string)$needle !== '' && strncmp(
			$haystack, $needle, strlen($needle)
		) === 0;
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
		return $needle !== '' && substr(
			$haystack, -strlen($needle)
		) === (string)$needle;
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
		if (!is_array($array) || empty($array)) {
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

