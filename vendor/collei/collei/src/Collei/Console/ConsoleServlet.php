<?php
namespace Collei\Console;

use Collei\Servlets\Servlet;
use Collei\Console\ConsoleApp;
use Collei\Console\CommandLine;
use Closure;

/**
 *	Encapsulates the basic servlet to work with an assigned console 
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-08-xx
 */
class ConsoleServlet extends Servlet
{
	/**
	 *	@var bool $silent
	 */
	private $silent = false;

	/**
	 *	@var \Closure $closure
	 */
	private $closure = false;

	/**
	 *	calls the named method, if it exists
	 *
	 *	@param	string	$method
	 *	@param	array	$parameters	
	 *	@return	mixed
	 */
	public final function callAction(string $method, $parameters)
	{
		return parent::callAction('handle', $parameters);
	}

	/**
	 *	returns the assigned closure, if is there one
	 *
	 *	@return	\Closure
	 */
	public final function getClosure()
	{
		return $this->closure;
	}

	/**
	 *	assigns a closure
	 *
	 *	@param	\Closure	$closure
	 *	@return	void
	 */
	protected final function setClosure(Closure $closure)
	{
		$this->closure = $closure->bindTo($this);
	}

	/**
	 *	Mark the command to be silent, i.e., to supress all output
	 *
	 *	@param	bool	$silent
	 *	@return	void
	 */
	protected function setSilent(bool $silent)
	{
		$this->silent = $silent;
	}

	/**
	 *	Returns true if set to silent, false otherwise
	 *
	 *	@return	bool
	 */
	protected function silent()
	{
		return $this->silent;
	}

	/**
	 *	Calls another command from here, suppressing all its output
	 *
	 *	@param	string	$command	the command name (with appropriate arguments and/or options)	
	 *	@param	array	$arguments	arguments, if needed
	 *	@param	int		$flags		additional settings for this call
	 *	@return	mixed
	 */
	protected function invokeAnother(
		string $command,
		array $arguments = null,
		int $flags = ConsoleApp::CA_MODE_NONE
	) {
		if (is_null($arguments)) {
			$arguments = [];
		}
		//
		$app = ConsoleApp::start(
			CommandLine::parse($command, $arguments),
			$flags
		);
		//
		return $app->run();
	}
	
}

