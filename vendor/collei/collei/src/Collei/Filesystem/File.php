<?php
namespace Collei\Filesystem;

use Collei\Filesystem\Folder;

/**
 *	Encapsulates a generic file
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2022-04-xx
 */
class File
{
	/**
	 *	@var string $path
	 */
	protected $path;

	/**
	 *	@var string $type
	 */
	protected $type;

	/**
	 *	@var int $size
	 */
	protected $size;

	/**
	 *	@var string $extension
	 */
	protected $extension;

	/**
	 *	Builds and initializes a file instance
	 *
	 *	@param	string	$path
	 *	@param	string	$type
	 *	@param	int		$size
	 */
	public function __construct(string $path, string $type = null, int $size = 0)
	{
		$this->path = $path;
		$this->type = !empty($type) ? $type : pathinfo($path, PATHINFO_EXTENSION);
		$this->size = $size ?? @filesize($path);
		$this->extension = pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 *	@var	string	$path
	 *	@var	string	$type
	 *	@var	int		$size
	 *	@var	string	$extension
	 */
	public function __get($name)
	{
		if (in_array($name, ['path','type','size','extension']))
		{
			return $this->$name;
		}
		if ($name == 'name')
		{
			return pathinfo($this->path, PATHINFO_BASENAME);
		}
		if ($name == 'exists')
		{
			return $this->exists();
		}
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

	/**
	 *	Verify if the file exists
	 *
	 *	@return	bool
	 */
	public function exists()
	{
		return file_exists($this->path);
	}

	/**
	 *	Copy the file to the specified $destination, creating the
	 *	whole tree if needed
	 *
	 *	@param	string	$destination
	 *	@return	bool
	 */
	public function copyTo(string $destination)
	{
		logit(__METHOD__, 'tried copy a file to ' . $destination);

		if (Folder::createIfNotExists(dirname($destination)))
		{
			return copy($this->path, $destination);
		}

		return false;
	}

	/**
	 *	Move the file to the specified $destination, creating the
	 *	whole tree if needed
	 *
	 *	@param	string	$destination
	 *	@return	bool
	 */
	public function moveTo(string $destination)
	{
		if (Folder::createIfNotExists(dirname($destination)))
		{
			return rename($this->path, $destination);
		}

		return false;
	}

	/**
	 *	Creates a new file instance
	 *
	 *	@param	string	$fileName
	 *	@return	\Collei\Filesystem\File
	 */
	public static function make(string $fileName)
	{
		return new static($fileName);
	}

}


