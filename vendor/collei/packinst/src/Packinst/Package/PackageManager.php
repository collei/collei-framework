<?php

namespace Packinst\Package;

/*
 *	this explicit require is necessary
 *	because autold() is not active at this point
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'ArrayTokenScanner.php';

define('PACKINST_GROUND', dirname(__DIR__, 3));

use Packinst\Utils\ArrayTokenScanner;
use Packinst\Package\GitPackage;
use Packinst\Package\GithubPackage;
use Packinst\Package\Downloader\GitPackageDownloader;
use Packinst\Package\Installer\GitPackageInstaller;
use Collei\Utils\Files\TextFile;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DateTime;
use Closure;

/**
 *	@author	alarido.su@gmail.com
 *	@since	2022-06-19
 *
 *	Just basic capabilities of a Git package
 *
 */
final class PackageManager
{
	/**
	 *	@const string DS
	 */
	private const DS = DIRECTORY_SEPARATOR;

	/**
	 *	@const string REGEX_JSON_COMMENTS
	 */
	private const REGEX_JSON_COMMENTS = '#\\/\\*[^\\x00]*\\*\\/|\\/\\/[^\\r\\n]*(\\r\\n|[\\r\\n])#U';

	/**
	 *	@const string CONFIG_FILE
	 */
	private const CONFIG_FILE = PACKINST_GROUND . DIRECTORY_SEPARATOR . 'packinst-config.json';

	/**
	 *	@var @static object $PACKINST_CONFIG
	 */
	private static $PACKINST_CONFIG = null;

	/**
	 *	@var @static string $location;
	 */
	private static $location = null;

	/**
	 *	@var @static array $packageList;
	 */
	private static $packageList = [];

	/**
	 *	@var @static array $initialized;
	 */
	private static $initialized = false;

	/**
	 *	@var @static array $logList
	 */
	private static $logList = [];

	/**
	 *	@var @static callable $logListener
	 */
	private static $logListener = null;

	/**
	 *	Loads the config file if not yet.
	 *
	 *	@return	bool
	 */
	private static function initConfig()
	{
		if (!is_null(self::$PACKINST_CONFIG))
		{
			return false;
		}
		//
		if ($config = TextFile::from(self::CONFIG_FILE))
		{
			$json = \preg_replace(
				self::REGEX_JSON_COMMENTS, '', $config->getText()
			);
			//
			$jsonObj = \json_decode($json);
			//
			if (\json_last_error() != JSON_ERROR_NONE)
			{
				return false;
			}
			//
			self::$PACKINST_CONFIG = $jsonObj;
		}
		//
		return true;
	}

	/**
	 *	Checks whether the $package can be installed.
	 *
	 *	@param	string	$package
	 *	@return	bool
	 */
	private static function canAddPackage(string $package)
	{
		return self::installConfigCheck('disable_add', $package);
	}

	/**
	 *	Checks whether the $package can be removed.
	 *
	 *	@param	string	$package
	 *	@return	bool
	 */
	private static function canRemovePackage(string $package)
	{
		return self::installConfigCheck('disable_removal', $package);
	}

	/**
	 *	Scan the internal array config on installable/reovable packages.
	 *	Returns true if item is not present on explicit deny list or if
	 *	it is listed under the except subkey (if any).
	 *
	 *	@param	string	$section
	 *	@param	string	$package
	 *	@return	boolval(var)
	 */
	private static function installConfigCheck(string $section, string $package)
	{
		self::initConfig();
		//
		$package = strtolower($package);
		//
		if ($except = (self::$PACKINST_CONFIG->$section->except ?? []))
		{
			foreach ($except as $item)
			{
				if ($item == $package)
				{
					return true;
				}
				elseif (strpos($package, str_replace('/*', '', $item)) === 0)
				{
					return true;
				}
			}
		}
		//
		if ($list = (self::$PACKINST_CONFIG->$section->list ?? []))
		{
			foreach ($list as $item)
			{
				if ($item == $package)
				{
					return false;
				}
				elseif (strpos($package, str_replace('/*', '', $item)) === 0)
				{
					return false;
				}
			}
		}
		//
		return true;
	}

	/**
	 *	Calls the registered listener (if any). Returns $logEvent content.
	 *
	 *	@static
	 *	@param	string	$logEvent
	 *	@return	string
	 */
	private static function callListener(string $logEvent)
	{
		if (self::$logListener instanceof Closure) {
			$call = self::$logListener;
			$call($logEvent);
		}
		//
		return $logEvent;
	}

	/**
	 *	Logs events into the event Logger and calls a Listener (if any).
	 *
	 *	@static
	 *	@param	bool	$withTimeLabel
	 *	@param	string	...$things
	 *	@return	void
	 */
	private static function log(bool $withTimeLabel = true, string ...$things)
	{
		if ($withTimeLabel) {
			$date = '[' . (new DateTime())->format('Y-m-d H:i:s.u') . '] ';
		}
		//
		self::$logList[] = self::callListener(
			($date ?? '') . implode('', $things ?? [])
		);
	}

	/**
	 *	@const int PS_UPDATED
	 *	@const int PS_OUTDATED
	 *	@const int PS_NOT_INSTALLED
	 *	@const int PS_UNREACHABLE_REPO
	 *	@const int PS_UNDEFINED
	 */
	public const PS_UPDATED = 1;
	public const PS_OUTDATED = 2;
	public const PS_NOT_INSTALLED = 3;
	public const PS_NOT_VERIFIABLE = 97;
	public const PS_UNREACHABLE_REPO = 98;
	public const PS_UNDEFINED = 99;

	/**
	 *	@const array PS_MESSAGE
	 */
	public const PS_MESSAGE = [
		self::PS_UPDATED => 'Plugin is up-to-date',
		self::PS_OUTDATED => 'Plugin is outdated',
		self::PS_NOT_INSTALLED => 'Plugin not installed',
		self::PS_NOT_VERIFIABLE => 'Plugin could not be verified against repo',
		self::PS_UNREACHABLE_REPO => 'Remote repository could not be reached',
		self::PS_UNDEFINED => 'Undefined plugin state',
	];

	/**
	 *	@const string INIT_FILE
	 */
	public const INIT_FILE = 'init.php';

	/**
	 *	@const string INIT_CONTENT_REGEX
	 */
	public const INIT_CONTENT_REGEX = '#plat_plugin_register\\(\\s*\\[([^\\x00]*)\\]\\s*\\);#i';

	/**
	 *	Performs the scan of a php array code string and converts it 
	 *	in a live PHP array.
	 *
	 *	@param	string	$arrayCode
	 *	@return	array|false
	 */
	private static function arrayCodeToArray(string $arrayCode)
	{
		$ats = new ArrayTokenScanner();
		//
		try {
			return $ats->scan($arrayCode);
		} catch (Throwable $e) {
			return false;
		}
	}

	/**
	 *	Scans the specified package path for info on the plugin
	 *
	 *	@param	string	$packagePath
	 *	@return	array|bool
	 */
	private static function scanPackage(string $packagePath)
	{
		if (empty($packagePath) || !is_dir($packagePath)) {
			return false;
		}
		//
		$initFile = $packagePath . self::DS . self::INIT_FILE;
		//
		if (!file_exists($initFile)) {
			return false;
		}
		//
		if ($contents = file_get_contents($initFile)) {
			$data = [];
			//
			if (preg_match(self::INIT_CONTENT_REGEX, $contents, $data)) {
				$code = '[' . $data[1] . ']';
				//
				return self::arrayCodeToArray($code);
			}
		}
		//
		return false;
	}

	/**
	 *	Scans the 'vendor' folder for info on installed plugins
	 *
	 *	@return	bool
	 */
	private static function scanLocationForPackages()
	{
		if (empty(self::$location))
		{
			return false;
		}
		//
		self::$packageList = [];
		//
		$mainLocation = self::$location;
		$vendors = array_diff(scandir($mainLocation), ['..', '.']);
		//
		foreach ($vendors as $vendor) {
			$vendorLocation = $mainLocation . self::DS . $vendor;
			//
			if (!is_dir($vendorLocation)) {
				continue;
			}
			//
			$packages = array_diff(scandir($vendorLocation), ['..', '.']);
			//
			foreach ($packages as $package) {
				$packagePath = $vendorLocation . self::DS . $package;
				//
				if (!is_dir($packagePath)) {
					continue;
				}
				//
				if ($info = self::scanPackage($packagePath)) {
					$info['plugin_path'] = $packagePath;
					$info['classes_path'] = $packagePath . self::DS . $info['classes_folder'];
					//
					self::$packageList[$info['plugin']] = $info;
				}
			}
		}
		//
		return true;
	}

	/**
	 *	Performs removal of the vendor path IF AND ONLY IF empty
	 *
	 *	@param	string	$vendorDir
	 *	@return	bool
	 */
	private static function removeVendorIfEmpty(string $vendorDir)
	{
		$handle = opendir($vendorDir);
		//
		while (false !== ($entry = readdir($handle))) {
			if ($entry != '.' && $entry != '..') {
				closedir($handle);
				//
				return false;
			}
		}
		//
		closedir($handle);
		//
		rmdir($vendorDir);
		//
		return true;
	}

	/**
	 *	Performs removal of the given path
	 *
	 *	@param	string	$pluginName
	 *	@return	bool
	 */
	private static function removePluginFolder(string $path)
	{
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$path, RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		//
		foreach ($files as $fileinfo) {
			$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
			$todo($fileinfo->getRealPath());
		}
		//
		rmdir($path);
		//
		self::removeVendorIfEmpty(dirname($path));
	}

	/**
	 *	Set the listener for the log produced by the Package Manager.
	 *
	 *	@param	Closure	$listener
	 *	@return	void
	 */
	public static function setLogListener(Closure $listener)
	{
		self::$logListener = $listener;
	}

	/**
	 *	Defines the location of the 'vendor' folder
	 *
	 *	@param	string	$location
	 *	@return	void
	 */
	public static function setLocation(string $location)
	{
		self::$location = $location;
	}

	/**
	 *	Returns a list of all installed plugins or an associative array
	 *	with their info, with indexes named after their names.
	 *
	 *	@param	bool	$loadInfo = false
	 *	@return	array|false
	 */
	public static function getInstalledPackages(bool $loadInfo = false)
	{
		if (empty(self::$packageList)) {
			if (!self::scanLocationForPackages()) {
				return false;
			}
		}
		//
		$data = [];
		//
		if ($loadInfo) {
			foreach (self::$packageList as $name => $info) {
				$data[$name] = $info;
			}
		} else {
			foreach (self::$packageList as $name => $info) {
				$data[] = $name;
			}
		}
		//
		return $data;
	}

	/**
	 *	Performs removal steps for the given plugin
	 *
	 *	@param	string	$pluginName
	 *	@return	bool
	 */
	public static function remove(string $pluginName)
	{
		if (empty(self::$packageList)) {
			self::log(true, 'Package Manager not initialized.');
			return false;
		}
		//
		if (!self::canRemovePackage($pluginName)) {
			self::log(true, 'Package cannot be removed.');
			return false;
		}
		//
		if (array_key_exists($pluginName, self::$packageList)) {
			// obtain the path
			$path = self::$packageList[$pluginName]['plugin_path'];
			// removes all files
			self::removePluginFolder($path);
			// unset index from array info
			unset(self::$packageList[$pluginName]);
			//
			self::log(true, 'Successfully removed package: ', $pluginName);
			//
			return true;
		}
		//
		return false;
	}

	/**
	 *	Performs update steps for the given plugin
	 *
	 *	@param	string	$pluginName
	 *	@return	bool
	 */
	public static function update(string $pluginName)
	{
		if (
			!self::canAddPackage($pluginName) ||
			!self::canRemovePackage($pluginName)
		) {
			self::log(true, 'Package cannot be updated.');
			return false;
		}
		//
		if (self::checkPluginState($pluginName) !== self::PS_OUTDATED) {
			self::log(true, 'Package is already up-to-date.');
			return false;
		}
		//
		if (self::remove($pluginName)) {
			$git = new GithubPackage($pluginName);
			//
			if ($state = self::install($git, true)) {
				self::log(true, 'Successfully installed: ', $pluginName);
			}
			//
			return $state;
		}
		//
		return false;
	}

	/**
	 *	Verify the update state of the given plugin and returns one of
	 *	the following values:
	 *		PS_UPDATED			(1) - plugin is up-to-date
	 *		PS_OUTDATED			(2) - plugin is "old" (not up-to-date)
	 *		PS_NOT_INSTALLED	(3) - plugin not found
	 *		PS_NOT_VERIFIABLE	(97) - package could not be verified
	 *		PS_UNREACHABLE_REPO	(98) - the remote repo could not be reached
	 *		PS_UNDEFINED		(99) - package list was not initialized
	 *	Returns false otherwise.
	 *
	 *	@param	string	$pluginName
	 *	@return	int|bool
	 */
	public static function checkPluginState(string $pluginName)
	{
		if (empty(self::$packageList)) {
			if (empty(self::$location)) {
				return self::PS_UNDEFINED;
			}
			//
			return self::PS_NOT_INSTALLED;
		}
		//
		if (!array_key_exists($pluginName, self::$packageList)) {
			return self::PS_NOT_INSTALLED;
		}
		//
		$pluginInfo = self::$packageList[$pluginName];
		$git = (new GithubPackage($pluginName))->fetchRepositoryInfo();
		//
		if ($dbi = $git->defaultBranchInfo) {
			$sha = $dbi->commit->sha ?? '';
			$nodeid = $dbi->commit->node_id ?? '';
			//
			$sha_here = $pluginInfo['branch_details']['commit_sha'] ?? '';
			$nodeid_here = $pluginInfo['branch_details']['commit_node'] ?? '';
			//
			if (empty($sha) || empty($sha_here)) {
				return self::PS_NOT_VERIFIABLE;
			}
			//
			if (($sha == $sha_here) && ($nodeid == $nodeid_here)) {
				return self::PS_UPDATED;
			} else {
				return self::PS_OUTDATED;
			}
		}
		//
		return self::PS_UNREACHABLE_REPO;
	}

	/**
	 *	Performs installation steps for the given package
	 *
	 *	@param	Packinst\Package\GitPackage	$package
	 *	@param	bool	$fetchInfo = false
	 *	@return	bool
	 */
	public static function install(GitPackage $package, bool $fetchInfo = false)
	{
		if (empty($package)) {
			self::log(true, 'Empty or invalid package.');
			return false;
		}
		//
		if (!self::canAddPackage($package->getName())) {
			self::log(
				true, 'Package ', $package->getName(), ' cannot be installed.'
			);
			return false;
		}
		//
		if (self::checkPluginState($package->getName()) !== self::PS_NOT_INSTALLED) {
			self::log(
				true, 'Package ', $package->getName(), ' is already installed. ',
				'If you want to run an update, please hit ',
				"\r\n\tphp packinst update ", $package->getName(),
				"\r\n on console."
			);
			return false;
		}
		//
		if ($fetchInfo) {
			$package->fetchRepositoryInfo();
		}
		//
		$group = $package->getVendor();
		$project = $package->getProject();
		//
		$to_path = self::$location . self::DS . $group;
		$to_zip = $to_path . self::DS . $project . '.zip';
		//
		@mkdir($to_path, 0777, true);
		//
		$downloader = new GitPackageDownloader($package);
		//
		if ($downloader->downloadTo($to_zip)) {
			$callback = [self::class, 'log'];
			//
			return (new GitPackageInstaller())
				->setLogListener(function(...$event) use ($callback) {
					$callback(false, ...$event);
				})
				->setPackageDownloader($downloader)
				->install();
		}
		//
		return false;
	}

	
}

