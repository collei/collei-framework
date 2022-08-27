<?php
namespace Collei\Http\Uploaders;

use Collei\Filesystem\File;
use Collei\Http\FileUploader;

/**
 *	Encapsulates a uploaded file
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-04-xx
 */
class UploadedFile extends File
{
	/**
	 *	@var string $tempName
	 */
	private $tempName;

	/**
	 *	@var string $name
	 */
	private $name;

	/**
	 *	@var string $fieldName
	 */
	private $fieldName;

	/**
	 *	@var int $fieldIndex
	 */
	private $fieldIndex;

	/**
	 *	@var string $savedName
	 */
	private $savedName = '';

	/**
	 *	@var string $savedPath
	 */
	private $savedPath = '';

	/**
	 *	@var bool $moved
	 */
	private $moved = false;

	/**
	 *	Builds and initializes a uploaded file instance
	 *
	 *	@param	string	$tempName
	 *	@param	string	$name
	 *	@param	string	$type
	 *	@param	int		$size
	 *	@param	string	$origin
	 *	@param	int		$originIndex
	 */
	private function __construct(
		string $tempName,
		string $name,
		string $type = null,
		int $size = 0,
		string $origin = null,
		int $originIndex = null
	)
	{
		parent::__construct(
			$tempName,
			'',
			(($size > 0) ? $size : filesize($tempName))
		);

		$this->name = $name;
		$this->tempName = $tempName;
		$this->type = pathinfo($name, PATHINFO_EXTENSION);
		$this->extension = pathinfo($name, PATHINFO_EXTENSION);
		$this->fieldName = $origin;
		$this->fieldIndex = $originIndex;
	}

	/**
	 *	@var	string	$tempName
	 *	@var	string	$savedName
	 *	@var	string	$savedPath
	 *	@var	bool	$moved
	 *	@var	string	$name
	 *	@var	string	$type
	 *	@var	int		$size
	 *	@var	string	$extension
	 *	@var	string	$fieldName
	 *	@var	int		$fieldIndex
	 *	@var	bool	$exists
	 */
	public function __get($name)
	{
		$valid = [
			'tempName',
			'savedName',
			'savedPath',
			'moved',
			'name',
			'type',
			'size',
			'extension',
			'fieldName',
			'fieldIndex',
		];

		if (in_array($name, $valid))
		{
			return $this->$name;
		}
		if ($name == 'exists')
		{
			return $this->exists();
		}
	}

	/**
	 *	Verify if this uploaded file has an index
	 *
	 *	@return	bool
	 */
	public function hasIndex()
	{
		return ($this->fieldIndex > 0) || ($this->fieldIndex === 0);
	}

	/**
	 *	Verify if the file exists and if it was not moved yet
	 *
	 *	@return	bool
	 */
	public function exists()
	{
		return file_exists($this->tempName) && !$this->moved;
	}

	/**
	 *	Move the file to the specified $folder
	 *
	 *	@param	string	$folder
	 *	@param	string	$withName
	 *	@return	bool
	 */
	public function moveTo(string $folder, string $withName = null)
	{
		$savedName = !is_null($withName)
			? ($withName . '.' . $this->extension)
			: $this->name;

		$destination = $folder . DIRECTORY_SEPARATOR . $savedName;

		if ($this->moved = move_uploaded_file($this->tempName, $destination))
		{
			$this->savedName = $savedName;
			$this->savedPath = $destination;
		}

		return $this->moved;
	}

	/**
	 *	Creates a new uploaded file instance
	 *
	 *	@param	string	$tempName
	 *	@param	string	$name
	 *	@param	string	$type
	 *	@param	int		$size
	 *	@param	string	$origin
	 *	@param	int		$originIndex
	 *	@return	\Collei\Http\Uploaders\UploadedFile
	 */
	public static function make(
		string $tempName,
		string $name = '',
		string $type = null,
		int $size = 0,
		string $origin = null,
		int $originIndex = null
	)
	{
		return new static(
			$tempName, $name, $type, $size, $origin, $originIndex
		);
	}

}


