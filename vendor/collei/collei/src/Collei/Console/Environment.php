<?php
namespace Collei\Console;

use Collei\Utils\Collections\Properties;
use Collei\Utils\Files\TextFile;

/**
 *	This encapsulates the desired behaviour of environment variables
 *	that may be kept along several Cyno command sessions and runnings.
 *	Now available (coming soon) at Cyno command initiator 
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-11-30
 */
class Environment
{
	/**
	 *	@var \Collei\Utils\Collections\Properties
	 */
	private $variables;

	/**
	 *	@var string
	 */
	private $fileName;

	/**
	 *	Parses the very straightforward-and-simple internal format
	 *	and fills the variables (again?)
	 *
	 *	@param	array	lines coming from, e.g., a file	
	 */
	protected function parse(array $lines)
	{
		foreach ($lines as $line)
		{
			$line = trim($line);
			$ex = explode(':', $line, 2);
			$this->variables->set($ex[0], $ex[1] ?? '');
		}
	}

	/**
	 *	Retrieves all variables in several lines in the internal format
	 *	to be written to a file
	 *
	 *	@return	array	
	 */
	protected function unparse()
	{
		$lines = [];
		$values = $this->variables->asArray();

		foreach ($values as $n => $v)
		{
			$lines[] = $n . ':' . $v;
		}

		return $lines;
	}

	/**
	 *	Instantiates a new Environment and loads previous variables
	 *
	 *	@param	string	$fileName
	 */
	public function __construct(string $fileName = null)
	{
		$this->fileName = $fileName ?? '';
		$this->variables = new Properties();

		$this->load();
	}

	/**
	 *	Saves all variables and terminates
	 *
	 *	@return void	
	 */
	public function __destruct()
	{
		$this->save();
	}

	/**
	 *	Loads from the file
	 *
	 *	@return	void
	 */
	public function load()
	{
		if (!empty($this->fileName))
		{
			$file = new TextFile();
			$file->loadFrom($this->fileName);
			$lines = $file->getLines();
			$this->parse($lines);
		}
	}

	/**
	 *	Saves to the file
	 *
	 *	@return	void
	 */
	public function save()
	{
		if (!empty($this->fileName))
		{
			$lines = $this->unparse();
			$file = new TextFile();
			$file->writeLines($lines);
			$file->saveTo($this->fileName);
		}
	}

	/**
	 *	Sets a variable
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value		
	 *	@return	void
	 */
	public function set(string $name, $value)
	{
		$this->variables->set($name, $value);
	}

	/**
	 *	Gets the variable, if any
	 *
	 *	@param	string	$name
	 *	@return	string|null
	 */
	public function get(string $name)
	{
		return $this->variables->get($name);
	}

	/**
	 *	Returns the names set in the environment
	 *
	 *	@return	array
	 */
	public function names()
	{
		return $n = $this->variables->asArrayOfNames();
	}

}


