<?php
namespace Collei\App;

use Collei\App\Loaders\ClassLoader;
use Collei\App\App;

/**
 *	Embodies URL properties
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class Environment 
{
	/**
	 *	@static
	 *	@var array $sites
	 */
	private static $sites = [];

	/**
	 *	@static
	 *	@var array $plugins
	 */
	private static $plugins = [];

	/**
	 *	@static
	 *	@var array $variables
	 */
	private static $variables = [];


	/**
	 *	Keeps plugin tracking for the system
	 *
	 *	@static
	 *	@param	string	$pluginName
	 *	@param	array	$info
	 *	@return	void
	 */
	public static function registerPlugin(string $pluginName, array $info)
	{
		self::$plugins[$pluginName] = $info;
	}

	/**
	 *	Returns a list of the loaded plugins' names.
	 *
	 *	@static
	 *	@return	array
	 */
	public static function listPlugins()
	{
		return array_keys(self::$plugins);
	}

	/**
	 *	Returns a list of the loaded plugins and their info.
	 *
	 *	@static
	 *	@return	array
	 */
	public static function listPluginsInfo()
	{
		return ($list = self::$plugins);
	}

	/**
	 *	Register the given site with the framework system.
	 *
	 *	@static
	 *	@param	string	$siteName
	 *	@return	void
	 */
	public static function registerSite(string $siteName)
	{
		$siteSource = PLAT_SITES_GROUND . DIRECTORY_SEPARATOR
			. $siteName . DIRECTORY_SEPARATOR
			. PLAT_SITES_CLASSES_ROOT_FOLDER;
		//
		self::$sites[$siteName] = array(
			'site' => $siteName,
			'source' => $siteSource,
		);
	}

	/**
	 *	Detects existing sites and registers them with the framework system.
	 *
	 *	@static
	 *	@return	void
	 */
	public static function registerSites()
	{
		$sitesFolder = PLAT_SITES_GROUND;
		$siteSubFolders = array_diff(
			scandir($sitesFolder), array('..', '.')
		);
		//
		foreach ($siteSubFolders as $subFolder) {
			if (is_dir($sitesFolder . DIR_SEP . $subFolder)) {
				self::registerSite($subFolder);
			}
		}
	}

	/**
	 *	Returns a list of the registered sites.
	 *
	 *	@static
	 *	@return	array
	 */
	public static function listSites()
	{
		return array_keys(self::$sites);
	}

	/**
	 *	Returns a list of the registered sites plus their info.
	 *
	 *	@static
	 *	@return	array
	 */
	public static function listSitesInfo()
	{
		return ($list = self::$sites);
	}

	/**
	 *	Sets a value to a variable in the App environment
	 *
	 *	@static
	 *	@param	string	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public static function setAppEnv(string $name, $value)
	{
		self::$variables[$name] = $value;
	}

	/**
	 *	Gets value from a variable in the App environment
	 *
	 *	@static
	 *	@param	string	$name
	 *	@return	mixed|null
	 */
	public static function getAppEnv(string $name)
	{
		return self::$variables[$name] ?? null;
	}

	/**
	 *	Checks for presence of $className and returns false if
	 *	it does not exist.
	 *
	 *	@static
	 *	@param	string	$className
	 *	@return	bool
	 */
	public static function hasClass(string $className = null)
	{
		if (empty($className)) {
			return false;
		}
		//
		autold_logger(
			__METHOD__, "try loading $className from environment."
		);
		//
		ClassLoader::requireManagerClass(
			$className
		);
		//
		if ($site = App::getInstance()->getSite()) {
			autold_logger(
				__METHOD__, "try loading $className from site $site."
			);
			//
			ClassLoader::requireSiteClass(
				$site, $className
			);
		}
		//
		return class_exists($className, true);
	}

}

