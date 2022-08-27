<?php
namespace Collei\Filesystem;

use Collei\Filesystem\File;
use Collei\Utils\Str;
use Collei\Utils\Arr;
use UnexpectedValueException;

/**
 *	Encapsulates a generic folder
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2022-04-28
 */
class Folder
{
	/**
	 *	@var string $path
	 */
	private $path;

	/**
	 *	Instantiates a new Folder object
	 *	@static
	 */
	public function __construct(string $path)
	{
		$this->path = $path;
	}

	/**
	 *	Returns whether $path exists 
	 *
	 *	@param	string	$relative
	 *	@return	string
	 */
	public function exists()
	{
		return is_dir($this->path);
	}

	/**
	 *	Get a subfolder object
	 *
	 *	@param	string	$name
	 *	@return	\Collei\Filesystem\Folder
	 */
	public function getFolder(string $name)
	{
		return new self(
			$this->path . DIRECTORY_SEPARATOR . Folder::absolutize($name)
		);
	}

	/**
	 *	Creates a subfolder
	 *
	 *	@param	string	$relative
	 *	@return	\Collei\Filesystem\Folder|null
	 */
	public function createFolder(string $name)
	{
		if (!$this->exists())
		{
			return null;
		}

		$subpath = $this->path . DIRECTORY_SEPARATOR . $name;

		if (mkdir($subpath, 0777))
		{
			return new static($subpath);
		}

		return null;
	}

	/**
	 *	Returns a list of objects (files and folders) inside the folder,
	 *	provided the folder exists. If not, returns false.
	 *
	 *	@return	array|bool
	 */
	public function filesOrFolders()
	{
		if (!$this->exists())
		{
			return false;
		}

		$items = Arr::except(scandir($this->path), ['.','..']);
		$objects = [];

		foreach ($items as $item)
		{
			$real_item = $this->path . DIRECTORY_SEPARATOR . $item;

			if (is_file($real_item))
				$objects[] = File::make($real_item);
			elseif (is_dir($real_item))
				$objects[] = self::make($real_item);
		}

		return $objects;
	}

	/**
	 *	Returns a list of files in the folder, provided the folder exists.
	 *	If not, returns false.
	 *
	 *	@return	array|bool
	 */
	public function files()
	{
		if ($items = $this->filesOrFolders())
		{
			return Arr::filterByCustom($items, function($v, $k){
				return ($v instanceof File);
			});
		}

		return false;
	}

	/**
	 *	Returns a list of subfolders in the folder, provided the folder exists.
	 *	If not, returns false.
	 *
	 *	@return	array|bool
	 */
	public function folders()
	{
		if ($items = $this->filesOrFolders())
		{
			return Arr::filterByCustom($items, function($v, $k){
				return ($v instanceof Folder);
			});
		}

		return false;
	}

	/**
	 *	Copies the whole tree (files and subfolders) to the $destination folder.
	 *	Returns true in case of success, false otherwise.
	 *	Throws UnexpectedValueException when the destination folder is a
	 *	subfolder of the current folder with $includeSubfolders set to true.
	 *
	 *	@param	\Collei\Filesystem\Folder|string	$destination
	 *	@param	bool	$includeSubfolders
	 *	@return	bool
	 *	@throws	\UnexpectedValueException
	 */
	public function copyTo($destination, bool $includeSubfolders = false)
	{
		if (!$this->exists())
		{
			return false;
		}

		// if $destination is a string (e.g., a folder name or tree)
		// we first instantiates a new Folder in behalf of it
		if (!($destination instanceof self))
		{
			if (!is_string($destination))
			{
				return false;
			}

			$destination = self::make($destination);
		}

		if (!$destination->exists())
		{
			return false;
		}

		$files = $this->files();
		$result = true;

		// copies files
		foreach ($files as $file)
		{
			$result = $result && $file->copyTo(
				$destination->path . DIRECTORY_SEPARATOR . $file->name
			);
		}

		// if recursive copy is requested,
		// we will do it recursively
		if ($includeSubfolders)
		{
			if (Str::startsWith($destination->path, $this->path))
			{
				throw new UnexpectedValueException(
					'The destination path is a subfolder of the source path.'
				);
			}

			$folders = $this->folders();

			foreach ($folders as $folder)
			{
				$subpath = $destination->path . DIRECTORY_SEPARATOR . $folder->name;

				if (Folder::createIfNotExists($subpath))
				{
					$result = $result && $folder->copyTo($subpath, true);
				}
			}
		}

		return $result;
	}

	/**
	 *	@property string $name
	 *	@property string $path
	 *	@property string $real
	 *	@property string $realpath
	 *	@property string $exists
	 */
	public function __get($name)
	{
		if ($name == 'name')
		{
			return basename($this->path);
		}
		if ($name == 'path')
		{
			return $this->path;
		}
		if ($name == 'exists')
		{
			return $this->exists();
		}
	}

	/**
	 *	Debug info through var_dump, print_r, and so on
	 *
	 *	@return	array
	 */
	public function __debugInfo()
	{
		return [
			'name' => basename($this->path),
			'path' => $this->path,
			'exists' => $this->exists(),
		];
	}

	/**
	 *	Converts itself to string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->path;
	}

	//--------------------------------------//
	//		folder and path utilities		//
	//--------------------------------------//

	/**
	 *	Returns an absolutized version of the given $relative path
	 *
	 *	@static
	 *	@param	string	$relative
	 *	@return	string
	 */
	public static function absolutize(string $relative)
	{
		// removes repetead and replaces all with real separator
		$frugalis = preg_replace('#(\\/+|\\\\+)#', DIRECTORY_SEPARATOR, $relative);

		// break it into pieces
		$theory = Str::explode(DIRECTORY_SEPARATOR, $frugalis);

		$absolutes = [];

		// process path relativizers...
		foreach ($theory as $piece) {
			if ($piece === '..')
			{
				array_pop($absolutes);
			}
			elseif ($piece !== '.')
			{
				$absolutes[] = $piece;
			}
		}

		// ...and bring them to path aboslutism
		return Str::trimSuffix(
			Arr::join(DIRECTORY_SEPARATOR, $absolutes), DIRECTORY_SEPARATOR
		);
	}

	/**
	 *	Returns a realized version of the given $virtual path, even
	 *	those non-existent ones
	 *
	 *	@static
	 *	@param	string	$virtual
	 *	@return	string
	 */
	public static function realize(string $virtual = '.')
	{
		if ($virtual == '/')
		{
			return PLAT_GROUND;
		}

		if (Str::startsWith($virtual, '/'))
		{
			return PLAT_GROUND . Str::replace('/', DIRECTORY_SEPARATOR, $virtual);
		}

		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$local_ground = dirname($bt[0]['file'] ?? '.');

		return static::absolutize($local_ground . DIRECTORY_SEPARATOR . $virtual);
	}

	/**
	 *	Instantiates a new Folder with the $tree
	 *
	 *	@static
	 *	@param	string	$tree
	 *	@return	\Collei\Filesystem\Folder
	 */
	public static function make(string $tree)
	{
		return new self($tree);
	}

	/**
	 *	Creates a folder (and a whole tree up there, if needed)
	 *	Returns true if creation is successful, false otherwise
	 *
	 *	@param	string	$tree
	 *	@return	bool
	 */
	public static function create(string $tree)
	{
		// normalizes the path separators
		$tree = preg_replace('#(\\\\+|\\/+)#', DIRECTORY_SEPARATOR, $tree);

		// the core line of this piece of code
		return mkdir($tree, 0777, TRUE);
	}

	/**
	 *	Creates a folder (and a whole tree up there, if needed)
	 *	Returns true if creation is successful or it already exists
	 *	Returns false otherwise
	 *
	 *	@param	string	$tree
	 *	@return	bool
	 */
	public static function createIfNotExists(string $tree)
	{
		if (!is_dir($tree))
		{
			return self::create($tree);
		}

		return true;
	}

}
