<?php
namespace Collei\Platform\Pieces;

use Collei\Support\Files\TextFile;
use Collei\Support\Str;

/**
 *	Encapsulates a stub file
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-04-13
 */
class Stub
{
	/**
	 *	@var string $path
	 */
	private $path = '';

	/**
	 *	@var bool $loaded
	 */
	private $loaded = false;

	/**
	 *	@var \Collei\Support\Files\TextFile $content
	 */
	private $content = null;

	/**
	 *	Loads the stub content from the name and category
	 *
	 *	@param	string	$name	name of the stub file
	 *	@param	string	$category	name of the category
	 *	@return	\Collei\Platform\Pieces\Stub
	 */
	private function loadFrom(string $name, string $category): Stub
	{
		$this->path = static::$location . DIRECTORY_SEPARATOR
						. $category . DIRECTORY_SEPARATOR
						. $name . PLAT_STUB_SUFFIX;
		//
		if (file_exists($this->path)) {
			$thing = ($this->content = TextFile::from($this->path));
			//
			$this->loaded = !empty($thing);
		} else {
			$this->loaded = false;
		}
		//
		return $this;
	}

	/*--------------------------*\
	 *		public members		*
	\*--------------------------*/

	/**
	 *	Fetch variables in the content and returns the transformed result
	 *
	 *	@param	array	$values		associative array of values
	 *	@return	string
	 */
	public function fetch(array $values): string
	{
		$text = '';
		//
		if ($this->content) {
			$text = $this->content->getText();
			//
			foreach ($values as $n => $v) {
				$text = Str::replace(('{' . $n . '}'), $v, $text);
			}
		}
		//
		return $text;
	}

	/*--------------------------*\
	 *		static helpers		*
	\*--------------------------*/

	/**
	 *	@var @static string $location
	 */
	private static $location = PLAT_STUB_GROUND;

	/**
	 *	Creates a new Stub already loaded and ready to go
	 *
	 *	@param	string	$name	name of the stub file
	 *	@param	string	$category	name of the category
	 *	@return	\Collei\Platform\Pieces\Stub|null
	 */
	public static function load(string $name, string $category): ?Stub
	{
		$stub = (new static())->loadFrom($name, $category);
		//
		if ($stub->loaded) {
			return $stub;
		}
		//
		return null;
	}

}

