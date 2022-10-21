<?php
namespace Collei\Console;

use Collei\Support\Collections\Properties;
use Collei\Support\Str;

/**
 *	Encapsulates the command line
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class CommandLine
{
	/**
	 *	@var string
	 */
	private $line = '';

	/**
	 *	@var string
	 */
	private $command = '';

	/**
	 *	@var string
	 */
	private $subCommand = '';

	/**
	 *	@var array
	 */
	private $arrayd = [];

	/**
	 *	@var array
	 */
	private $arguments = [];

	/**
	 *	@var array
	 */
	private $options = [];

	/**
	 *	Creates a new instance of CommandLine
	 *
	 *	@param	array	command line (from $_SERVER['argv'])	
	 */
	private function __construct(array $command)
	{
		$this->arrayd = $command;
		$command_items = [];
		//
		$patt = '#^([^\s\:]+)(\:([^\s]+))?\s+#i';
		if (preg_match($patt, ltrim($command[0] ?? ''), $data)) {
			$this->command = $data[1];
			$this->subCommand = $data[3] ?? null;
		}
		//
		foreach ($command as $item) {
			if (strpos($item, ' ') === false) {
				$command_items[] = $item;
			} else {
				$command_items[] = '"' . $item . '"';
			}
		}
		//
		$this->line = trim(implode(' ', $command_items));
	}

	/**
	 *	Returns the command line as a string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->line;
	}

	/**
	 *	Returns if the specified value or option exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function has(string $name)
	{
		return $this->hasArgument($name) || $this->hasOption($name);
	}

	/**
	 *	Returns the specified value - if it exists - or the default
	 *
	 *	@param	string	$name	
	 *	@param	mixed	$default	
	 *	@return	mixed|null
	 */
	public function get(string $name, $default = null)
	{
		if ($this->hasArgument($name)) {
			return $this->getArgument($name, $default);
		}
		//
		if ($this->hasOption($name)) {
			return $this->getOption($name);
		}
		//
		return $default;
	}

	/**
	 *	Returns the subcommand (if any).
	 *
	 *	@return	string|null
	 */
	public function getSubCommand()
	{
		return $this->subCommand;
	}

	/**
	 *	Returns if the subcommand (if any) matches $name
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function isSubCommand(string $name)
	{
		return $name === $this->subCommand;
	}

	/**
	 *	Returns if the specified argument exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function hasArgument(string $name)
	{
		return array_key_exists($name, $this->arguments);
	}

	/**
	 *	Returns the specified argument - if it exists - or the default
	 *
	 *	@param	string	$name	
	 *	@param	mixed	$default	
	 *	@return	mixed|null
	 */
	public function getArgument(string $name, $default = null)
	{
		return $this->arguments[$name] ?? $default;
	}

	/**
	 *	Returns an array of all arguments
	 *
	 *	@return	array
	 */
	public function getArguments()
	{
		return $copy = $this->arguments;
	}

	/**
	 *	Returns the specified option - if it exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function hasOption(string $name)
	{
		return array_key_exists($name, $this->options);
	}

	/**
	 *	Returns the specified option - if it exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function getOption(string $name)
	{
		return $this->arguments[$name] ?? false;
	}

	/**
	 *	Returns an array of all options
	 *
	 *	@return	array
	 */
	public function getOptions()
	{
		return $copy = $this->options;
	}

	/**
	 *	Capture the command line from the server - ignoring the first 
	 *
	 *	@return	array
	 */
	public static function capture(array $arguments = null)
	{
		$args = [];
		//
		if (!is_null($arguments)) {
			$args = $arguments;
		} else {
			$args = $_SERVER['argv'] ?? [];
			//
			array_shift($args);
		}
		//
		return new static($args);
	}

	/**
	 *	Parses the command line and join the arguments, if possible, and
	 *	then returns a new CommandLine instance 
	 *
	 *	@return	\Collei\Console\CommandLine
	 */
	public static function parse(string $command, array $arguments = null)
	{
		$commandLine = new static(
			Str::tokenize($command)
		);
		//
		if (!is_null($arguments)) {
			foreach ($arguments as $k => $v) {
				if (Str::startsWith($k, '--')) {
					$commandLine->options[$k] = $v;
				} else {
					$commandLine->arguments[$k] = $v;
				}
			}
		}
		//
		return $commandLine;
	}

}

