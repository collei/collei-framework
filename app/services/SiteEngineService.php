<?php

namespace App\Services;

use Collei\Services\Service;
use Collei\Http\Request;
use Collei\Utils\Str;
use Collei\Platform\Pieces\Stub;

class SiteEngineService extends Service
{
	private static $subfolders = [
		PLAT_COMMANDS_FOLDER_NAME,
		PLAT_FILTERS_FOLDER_NAME,
		PLAT_MODELS_FOLDER_NAME,
		PLAT_SERVICES_FOLDER_NAME,
		PLAT_SERVLETS_FOLDER_NAME,
	];

	private static $dir = null;

	private static function makeTarget(string $dir, string $kind = null)
	{
		// initializes the variable if already not
		static::$dir = static::$dir
			?? ($dir . DIRECTORY_SEPARATOR . PLAT_SITES_CLASSES_ROOT_FOLDER);
		// returns appropriate file basepath
		return in_array($kind ?? '', static::$subfolders)
			? (static::$dir . DIRECTORY_SEPARATOR . $kind)
			: (static::$dir);
	}

	////////////////////////

	private function listFiles(string $engine, string $kind = 'model')
	{
		$list = [];
		//
		if ($dir = groundOf($engine)) {
			$target = static::makeTarget($dir, $kind);
			$files = array_diff(scandir($target), ['.','..']);
			//
			foreach ($files as $file) {
				$filepath = $target . DIRECTORY_SEPARATOR . $file;
				//
				if (
					is_file($filepath) && Str::endsWith($file, PLAT_CLASSES_SUFFIX)
				) {
					if (
						strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ', substr($file,0,1)) !== false
					) {
						$list[] = $file;
					}
				}
			}
		}
		//
		return $list;
	}

	private function getContentFor($classname, $element, array $variables = [])
	{
		if (in_array($element, self::$subfolders)) {
			if ($stub = Stub::load($element, PLAT_STUB_CATEGORY_CLASSES)) {
				$variables = \array_merge($variables, [
					'className' => $classname
				]);
				return $stub->fetch($variables);
			}
		}
		//
		return '';
	}


	///////////////////////

	public function listModels($engine)
	{
		return $this->listFiles($engine, 'models');
	}

	public function listServices($engine)
	{
		return $this->listFiles($engine, 'services');
	}

	public function listServlets($engine)
	{
		return $this->listFiles($engine, 'servlets');
	}

	public function listFilters($engine)
	{
		return $this->listFiles($engine, 'filters');
	}

	public function listCommands($engine)
	{
		return $this->listFiles($engine, 'commands');
	}

	public function createFile(
		$classname, $engine, $element, array $variables = []
	) {
		if (empty($classname) || empty($engine)) {
			return false;
		}
		//
		if ($dir = groundOf($engine)) {
			$subfolder = in_array($element, static::$subfolders)
				? $element
				: PLAT_MODELS_FOLDER_NAME;
			//
			$content = $this->getContentFor($classname, $element, $variables);
			$target = static::makeTarget($dir, $element);
			$targetFile = $target . DIRECTORY_SEPARATOR
				. $classname . PLAT_CLASSES_SUFFIX;
			//
			if (!is_dir($target) && !is_file($target)) {
				mkdir($target);
			}
			//
			file_put_contents($targetFile, $content);
			//
			return true;
		}
		//
		return false;
	}


}
