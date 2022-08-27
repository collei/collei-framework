<?php 
namespace Collei\Utils\Files;

use Collei\Filesystem\File;

/**
 *	Encapsulates a text file
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-xx
 */
class TextFile
{
	/**
	 *	@var array
	 */
	private $lines = array();

	/**
	 *	Load text from a file
	 *
	 *	@param	string	$fileName
	 *	@return	bool	true if successful
	 */
	private function load(string $fileName)
	{
		$result = file($fileName, FILE_TEXT | FILE_IGNORE_NEW_LINES);
		//
		if ($result !== FALSE)
		{
			$this->lines = $result;
			return true;
		}
		//
		return false;
	}

	/**
	 *	Save all text to the file
	 *
	 *	@param	string	$fileName
	 *	@param	bool	$append		true to append to an existent file, false to overwrite it
	 *	@return	bool	true if successful
	 */
	private function save(string $fileName, bool $append = false)
	{
		$data = implode(PHP_EOL, $this->lines);
		//
		if ($append)
		{
			$result = file_put_contents($fileName, $data, FILE_APPEND);
		}
		else
		{
			$result = file_put_contents($fileName, $data);
		}
		//
		if ($result !== FALSE)
		{
			return true;
		}
		return false;
	}

	/**
	 *	Creates a new instance
	 *
	 */
	public function __construct()
	{
	}

	/**
	 *	Load text from a file path
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	string	$fileName
	 *	@return	bool
	 */
	public function loadFrom(string $fileName)
	{
		return $this->load($fileName);
	}

	/**
	 *	Load text from a file path.
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	\Collei\Filesystem\File	$file
	 *	@return	bool
	 */
	public function loadFromFile(File $file)
	{
		return $this->load($file->path);
	}

	/**
	 *	Save all text to the file, overwriting existent file (if any)
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	string	$fileName
	 *	@return	bool
	 */
	public function saveTo(string $fileName)
	{
		return $this->save($fileName);
	}

	/**
	 *	Save all text to the file, overwriting existent file (if any)
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	\Collei\Filesystem\File	$file
	 *	@return	bool
	 */
	public function saveToFile(File $file)
	{
		return $this->save($file->path);
	}

	/**
	 *	Save all text to the file, appending to an existent (if any)
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	string	$fileName
	 *	@return	bool
	 */
	public function appendTo(string $fileName)
	{
		return $this->save($fileName, true);
	}

	/**
	 *	Save all text to the file, appending to an existent (if any)
	 *	Returns true if successful, false otherwise
	 *
	 *	@param	\Collei\Filesystem\File	$file
	 *	@return	bool
	 */
	public function appendToFile(File $file)
	{
		return $this->save($file->path, true);
	}

	/**
	 *	Returns all lines at once 
	 *
	 *	@return	array
	 */
	public function getLines()
	{
		return $this->lines;
	}

	/**
	 *	Returns the whole content as string
	 *
	 *	@return	string
	 */
	public function getText()
	{
		return implode(PHP_EOL, $this->lines);	
	} 

	/**
	 *	Adds text to the current line
	 *
	 *	@param	mixed	$text
	 *	@return	void
	 */
	public function write($text)
	{
		$many = count($this->lines);
		if ($many < 1) {
			$this->lines[0] = '';
			$many = 1;
		}
		$this->lines[$many-1] .= (''.$text.'');
	}

	/**
	 *	Adds text to the current line, then adds a new line
	 *
	 *	@param	mixed	$text
	 *	@return	void
	 */
	public function writeLine($text = '')
	{
		$this->write($text);
		$this->lines[] = '';
	}

	/**
	 *	Adds one or more lines at once 
	 *
	 *	@param	array	$lines
	 *	@return	void
	 */
	public function writeLines(array $lines)
	{
		foreach ($lines as $line)
		{
			$this->lines[] = $line;
		}
	}


	/**
	 *	Builds a new instance from a text file at once 
	 *
	 *	@param	string	$fileName
	 *	@return	\Collei\Utils\Files\TextFile|null
	 */
	public static function from(string $fileName)
	{
		$file = new static();

		if ($file->loadFrom($fileName))
		{
			return $file;
		}

		return null;
	}

	/**
	 *	Builds a new instance from a File instance at once 
	 *
	 *	@param	\Collei\Filesystem\File	$file
	 *	@return	\Collei\Utils\Files\TextFile|null
	 */
	public static function fromFile(File $file)
	{
		$file = new static();

		if ($file->loadFromFile($file))
		{
			return $file;
		}

		return null;
	}

}


