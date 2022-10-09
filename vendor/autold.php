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
 *	loads the autoloader class and initializes it
 */
require_once PLAT_VENDOR_GROUND . '/collei/collei/src/Collei/App/Loaders/ClassAutoloader.php';
//
\Collei\App\Loaders\ClassAutoloader::init();

/*
 *	loads the Collei/Packinst plugin manager
 *	and initializes it
 */
require_once PLAT_VENDOR_GROUND . '/collei/packinst/src/Packinst/Package/PackageManager.php';
//
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

/*
 *	Initializes debug level notification flags
 */
senvs([
	'debug-errors' => true,
	'debug-warnings' => false,
	'debug-notices' => false,
	'debug-raws' => false,
]);

/*----------------------------------------------*
 *	below there are some utilitary functions	*
 *----------------------------------------------*/

/**
 *	Initializes the log keepers.
 *
 *	@return	void
 */
function init_loggers()
{
	register_shutdown_function(function() {
		\Collei\App\Logger::save('plat');
	});
}

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


