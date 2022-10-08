<?php
namespace Collei\App\Seekers;

use Collei\Utils\Str;

/**
 *	Embodies class loader capabilities
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-08
 */
class ClassSeeker
{
	private const FORM_NAMESPACE = '#namespace([^;]*)#';
	private const FORM_CLASS = '#class\\s+([^\\s]+)#';

	/**
	 *	@var array $instances
	 */
	private static $classes = [];

	/**
	 *	Extracts full classname (with namespace) from the PHP file
	 *
	 *	@param	string	$path
	 *	@return	string|false
	 */
	private static function extractClassName(string $path)
	{
		$handle = fopen($path, "r");
		$namespace = '';
		$classname = '';
		//
		while (($line = fgets($handle)) !== false) {
			if (strpos($line, 'namespace ') !== false) {
				if (preg_match(self::FORM_NAMESPACE, $line, $data)) {
					$namespace = trim($data[1]);
				}
			} elseif (strpos($line, 'class ') !== false) {
				if (preg_match(self::FORM_CLASS, $line, $data)) {
					$classname = trim($data[1]);
				}
			}
		}
		//
		fclose($handle);
		//
		if (!empty($classname)) {
			if (!empty($namespace)) {
				return "$namespace\\$classname"; 
			} else {
				return $classname;
			}
		}
		//
		return false;
	}

	/**
	 *	Performs class scan on the given $folder and internally stores them
	 *
	 *	@param	string	$folder
	 *	@return	array|false
	 */
	public static function scan(string $folder)
	{
		if (!is_dir($folder)) {
			return false;
		}
		//
		$files = scandir($folder);
		//
		if ($files === false) {
			return false;
		}
		//
		$files = array_diff($files, ['..','.']);
		$classes = [];
		//
		foreach ($files as $file) {
			if (Str::endsWith($file, PLAT_CLASSES_SUFFIX)) {
				$path = $folder . DIRECTORY_SEPARATOR . $file;
				$class = self::extractClassName($path);
				//
				self::$classes[$class] = $path;
				$classes[$class] = $path; 
			}
		}
		//
		return $classes;
	}

	/**
	 *	Retrieves the path for the given $class, if any
	 *
	 *	@param	string	$class
	 *	@return	string|false
	 */
	public static function seek(string $class)
	{
		return self::$classes[$class] ?? false;
	}

}

