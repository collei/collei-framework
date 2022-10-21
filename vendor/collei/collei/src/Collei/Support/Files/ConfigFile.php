<?php 
namespace Collei\Support\Files;

use Collei\Support\Files\TextFile;

/**
 *	Encapsulates a text file
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-xx
 */
class ConfigFile
{
	/**
	 *	@var array $variables
	 */
	private $variables = array();

	/**
	 *	@var string $fileName
	 */
	private $fileName = '';

	/**
	 *	@var \Collei\Support\Files\TextFile $file
	 */
	private $file = null;

	/**
	 *	@var array $temp
	 */
	private $temp = [];

	/**
	 *	Performs primary parsing and value collection and storage
	 *
	 *	@return	void
	 */
	private function firstPass()
	{
		$this->temp = $this->file->getLines();
		$title = '';
		foreach ($this->temp as $line)
		{
			$line = trim($line);
			if (\str_starts_with($line, ';') || str_starts_with($line, '#')) {
				// ignores line
			} elseif ($line!='') {
				if (\str_starts_with($line, '[') && str_ends_with($line, ']')) {
					$title = trim(\substr($line, 1, -1));
				} else {
					$line = \explode('=', $line, 2);
					$name = ($title!='') ? ($title.'.'.trim($line[0])) : (trim($line[0]));
					$value = trim($line[1]);
					$this->variables[$name] = $value;
				}
			}
		}
	}

	/**
	 *	Performs additional variable resolution and substitution 
	 *
	 *	@return	void
	 */
	private function secondPass()
	{
		foreach ($this->variables as $id => $value)
		{
			$from = 0;
			$slen = \strlen($value) - 1;
			$bs = \strpos($value, '{');
			while ($bs !== false && $from < $slen) {
				$be = \strpos($value, '}', $bs+1);
				$subname = \substr($value, $bs+1, $be-$bs-1);
				if (isset($this->variables[$subname])) {
					$value = \str_replace('{'.$subname.'}', $this->variables[$subname], $value);
					$this->variables[$id] = $value;
				}
				$from = $be+1;
				$slen = \strlen($value) - 1;
				if ($from >= $slen) {
					$bs = false;
				} else {
					$bs = \strpos($value, '{', $from);
				}
			}
		}
	}

	/**
	 *	Performs initialization
	 *
	 *	@return	void
	 */
	private function initialize()
	{
		$this->file = new TextFile();
		$this->file->loadFrom($this->fileName);
		$this->firstPass();
		$this->secondPass();
		$this->secondPass();
		$this->file = null;
		$this->temp = null;
	}

	/**
	 *	Builds and instantiates a configuration file handler
	 *
	 *	@param	string	$fileName
	 */
	public function __construct(string $fileName)
	{
		$this->fileName = $fileName;
		$this->initialize();
	}

	/**
	 *	Returns the configuration value, with a possible alternative, if desired
	 *
	 *	@param	string	$configName
	 *	@param	string	$alternative
	 *	@return	string
	 */
	public function get(string $configName, string $alternative = null)
	{
		if (isset($this->variables[$configName])) {
			return $this->variables[$configName];
		}
		return $alternative;
	}

	/**
	 *	Loads the given file immediately
	 *
	 *	@static
	 *	@param	string	$fileName
	 *	@return	\Collei\Support\Files\ConfigFile
	 *
	 */
	public static function from(string $fileName)
	{
		return new static($fileName);
	}

}


