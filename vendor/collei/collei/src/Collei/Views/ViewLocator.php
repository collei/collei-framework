<?php
namespace Collei\Views;

use Collei\App\App;
use Collei\Http\Session;
use Collei\Views\ConstructBase;
use Collei\Views\ColleiViewException;
use Collei\Views\ViewValidator;
use Collei\Support\Str;
use Collei\Support\Files\TextFile;
use Collei\Database\Yanfei\Model;
use Collei\Database\Yanfei\ModelResult;
use Exception;
use Throwable;
use ParseError;
use Closure;

/**
 *	Embodies the view locator methods
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class ViewLocator
{
	/**
	 *	Gathers the full path for the view source file of main platform site.
	 *
	 *	@static
	 *	@param	mixed	$viewName
	 *	@return	string|false
	 */
	public static function locateSystemViewFile(string $viewName)
	{
		$view = (!is_null($viewName) ? trim($viewName) : '');
		if (preg_match('/^\\w+(\\.\\w+)*$/', $view) === false) {
			return false;
		}
		//
		$viewFileName = PLAT_RESOURCE_VIEWS_GROUND	. DIRECTORY_SEPARATOR
			. str_replace('.', DIRECTORY_SEPARATOR, $view)
			. PLAT_VIEWS_SUFFIX;
		if (file_exists($viewFileName)) {
			return $viewFileName;
		}
		//
		return false;
	}

	/**
	 *	Gathers the full path for the view source for a given site.
	 *
	 *	@static
	 *	@param	mixed	$view_name
	 *	@param	mixed	$site_name
	 *	@param	mixed	&$site_from
	 *	@return	string|false
	 */
	public static function locateViewFile(
		string $viewName, string $siteName = null, string &$siteFrom = null
	) {
		$view = (!is_null($viewName) ? trim($viewName) : '');
		$siteFrom = '';
		//
		if (preg_match('/^\w+(\.\w+)*$/', $view) === false) {
			return false;
		}
		//
		if (is_null($siteName) || empty($siteName)) {
			$siteFrom = '';
			return self::locateSystemViewFile($viewName);
		}
		//
		$platformViewFileName = PLAT_RESOURCE_VIEWS_GROUND
			. DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view)
			. PLAT_VIEWS_SUFFIX;
		//
		$siteViewFileName = PLAT_SITES_GROUND . DIRECTORY_SEPARATOR
			. $siteName . DIRECTORY_SEPARATOR . PLAT_RESOURCE_VIEWS_FOLDER
			. DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view)
			. PLAT_VIEWS_SUFFIX;
		//
		if (file_exists($siteViewFileName)) {
			$siteFrom = $siteName;
			return $siteViewFileName;
		}
		//
		if (file_exists($platformViewFileName)) {
			return $platformViewFileName;
		}
		//
		return false;
	}

}

