<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 *	loads the very basic functions
 */
require_once __DIR__ . '/basefunc.php';

/*
 *	loads first line helpers
 */
require_once dirname(__DIR__) . '/helpers/main.php';

/*
 *	loads essential environment classes...
 */
require_once PLAT_VENDOR_GROUND . '/collei/collei/src/Collei/App/Environment.php';

/*
 *	loads the Collei/Packinst plugin manager...
 */
require_once PLAT_VENDOR_GROUND . '/collei/packinst/src/Packinst/Package/PackageManager.php';

/*
 *	register autoloader
 */
spl_autoload_register('autold');

/*
 * ...and initializes it
 */
init_plugin_manager();

/* 
 *	initializes loggers
 */
init_loggers();

/*
 *	register available sites (if any)
 */
\Collei\App\Environment::registerSites();

/*
 *	register listeners
 */
\Collei\App\Events\Registerers\GlobalEventsRegisterer::scanForListeners();

/**
 *	Loads and initializes the registered plugins
 *
 *	@return	void
 */
function init_plugin_manager()
{
	\Packinst\Package\PackageManager::setLocation(PLAT_VENDOR_GROUND);
	//
	$plugins = \Packinst\Package\PackageManager::getInstalledPackages(true);
	//
	foreach ($plugins as $name => $details) {
		\Collei\App\Environment::registerPlugin($name, $details);
	}
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
	return \Collei\App\Loaders\ClassLoader::requireSiteClass(
		$site_name, $class_name
	);
}

/**
 *	Loads Platform manager classes. For use of the \Collei\App\App class.
 *
 *	@param	mixed	$class_name
 *	@return	bool
 */
function require_manager_class($class_name)
{
	return \Collei\App\Loaders\ClassLoader::requireManagerClass(
		$class_name
	);
}

/**
 *	Logs autold issues
 *
 *	@param	mixed	$title
 *	@param	mixed	$message
 *	@param	mixed	$severity = null
 *	@return	void
 */
function autold_logger($title, $message, $severity = null)
{
	static $timesCalled = 0;
	// obeys logging control flag
	if (!(PLAT_LOGGING['classloader'] ?? true)) {
		return;
	}
	//
	$file = PLAT_LOGS_GROUND
		. DIRECTORY_SEPARATOR
		. '.plat-autold-' . date('Ymd') . '.log';
	//
	if ($timesCalled == 0) {
		$line = "\r\n\r\n-------------------[ start of log at "
			. (new \DateTime())->format('Y-m-d H:i:s.u')
			. ' ]--------------------';
		//
		file_put_contents($file, $line, FILE_APPEND);
	}
	//
	++$timesCalled;
	//
	$line = "\r\n"
		. '[' . (new \DateTime())->format('Y-m-d H:i:s.u') . '] '
		. '[' . ($severity ?? 'common_log') . '] '
		. ($title . ' -> ' . $message);
	//
	file_put_contents( $file, $line, FILE_APPEND);
}

/**
 *	The system classloader. Registered autoloader with PHP.
 *
 *	@param	mixed	$class
 *	@return	void
 */
function autold($class)
{
	$found = false;
	//
	// loads info about every registered plugin
	if ($plugins = plat_plugin_list_info()) {
		foreach ($plugins as $n => $plugin) {
			// builds the path to
			$tofind = preg_replace(
				'#(\\/+|\\\\+)#',
				DIRECTORY_SEPARATOR,
				($plugin['classes_path'] . '/' . $class . PLAT_CLASSES_SUFFIX)
			);
			//
			if (file_exists($tofind)) {
				// log it
				autold_logger('found', "from {$n}: {$class} in {$tofind}");
				// include it
				require_once $tofind;
				// and finishes here
				return;
			}
		}
	}
	//
	if (!$found) {
		$current_app = \Collei\App\App::getInstance();
		//
		if (!is_null($current_app)) {
			$site_name = $current_app->getSite();
			//
			if ($site_name != '') {
				autold_logger('found²', $class);
				//
				$found = require_site_class($site_name, $class);
			}
		}
	}
	//
	if (!$found) {
		autold_logger('found³ ', $class);
		//
		$found = require_manager_class($class);
	}
	//
	if (!$found) {
		autold_logger('not found', $class);
	}
}



senv('debug-errors', true);
senv('debug-warnings', false);
senv('debug-notices', false);
senv('debug-raws', false);


