<?php
namespace Collei\App\Loaders;

require_once dirname(__FILE__) . '/LoggerTrait.php';
require_once PLAT_VENDOR_GROUND . '/collei/collei/src/Collei/App/Environment.php';

use Collei\App\Environment;
use Collei\App\App;
use Collei\App\Loaders\ClassLoader;
use Collei\App\Loaders\LoggerTrait;
use DateTime;

/**
 *	Embodies class loader capabilities
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-08
 */
class ClassAutoloader
{
	use LoggerTrait;

	/**
	 *	@static
	 *	@var self $instance
	 */
	private static $instance = null;

	/**
	 *	Registers the loader with spl_autoload_register().
	 *
	 *	@return	self
	 */
	private function __construct()
	{
		spl_autoload_register(array($this, 'loader'));
	}

	/**
	 *	Loads classes on the fly as required.
	 *
	 *	@return	void
	 */
	private function loader($className)
	{
		$found = false;
		//
		// loads info about every registered plugin
		if ($plugins = Environment::listPluginsInfo()) {
			foreach ($plugins as $n => $plugin) {
				// builds the path to
				$tofind = preg_replace(
					'#(\\/+|\\\\+)#',
					DIRECTORY_SEPARATOR,
					($plugin['classes_path'] . '/' . $className . PLAT_CLASSES_SUFFIX)
				);
				//
				if (file_exists($tofind)) {
					// log it
					self::log('found', "from {$n}: {$className} in {$tofind}");
					// include it
					require $tofind;
					// and finishes here
					return;
				}
			}
		}
		//
		if (!$found) {
			$current_app = App::getInstance();
			//
			if (!is_null($current_app)) {
				$site_name = $current_app->getSite();
				//
				if ($site_name != '') {
					self::log('found[b]', $className);
					//
					$found = ClassLoader::requireSiteClass($site_name, $className);
				}
			}
		}
		//
		if (!$found) {
			self::log('found[c] ', $className);
			//
			$found = ClassLoader::requireManagerClass($className);
		}
		//
		if (!$found) {
			self::log('not found[c]', $className);
		}
	}

	/**
	 *	Initializes the autoloader once.
	 *
	 *	@return	void
	 */
	public static function init()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
	}

}

