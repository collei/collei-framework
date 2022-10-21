<?php
namespace Collei\Console;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use Collei\Console\ConsoleApp;
use Collei\Console\Commands\Command;
use Collei\App\Services\Service;

/**
 *	Encapsulates console servet dispatching
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-xx
 */
class ConsoleServletDispatcher
{
	/**
	 *	@var \Collei\Console\ConsoleApp
	 */
	private $app;

	/**
	 *	Invoke the closure
	 *
	 *	@param	\Closure	$closure	
	 *	@param	array		$parameters
	 *	@return	mixed
	 */
	private function callClosure(Closure $closure, array $parameters)
	{
		return call_user_func_array($closure, $parameters);
	}

	/**
	 *	Return the method parameters
	 *
	 *	@param	\ReflectionMethod|\ReflectionFunction	$refMethod	
	 *	@param	\Collei\Console\CommandLine	$commandLine
	 *	@return	array
	 */
	private function getMethodParameters($refMethod, CommandLine $commandLine)
	{
		$ref_parm_arr = $refMethod->getParameters();
		$parameters = array();
		//
		foreach ($ref_parm_arr as $ref_parm) {
			$name = $ref_parm->getName();
			$type = $ref_parm->getType();
			//
			if (!is_null($type)) {
				$type = $type->getName();
			} else {
				$type = '';
			}
			//
			if ($type === CommandLine::class) {
				$parameters[$name] = $commandLine;
			} elseif (is_subclass_of($type, Service::class)) {
				$parameters[$name] = $type::make();
			}
		}
		//
		return $parameters;
	}

	/**
	 *	Invoke the command with the commandline
	 *
	 *	@param	\Collei\Console\Commands\Command $command	
	 *	@param	\Collei\Console\CommandLine	$commandLine
	 *	@return	mixed
	 */
	private function callInstance(Command $command, CommandLine $commandLine)
	{
		if (!is_null($command)) {
			$cls_name = get_class($command);
			Command::incorporate($command, $commandLine);
			//
			// tells the servlet to supress output or not
			// (although it does not affect calls to PHP echo(), nor PHP error display)
			$command->setSilent($this->app->silent);
			//
			if ($cls_name == Command::class) {
				$closure = $command->getClosure();
				$ref_method = new ReflectionFunction($closure);
				$parameters = $this->getMethodParameters($ref_method, $commandLine);

				return $this->callClosure($closure, $parameters);
			} else {
				$ref_class = new ReflectionClass($command);
				$ref_method = $ref_class->getMethod('handle');
				$parameters = $this->getMethodParameters($ref_method, $commandLine);

				return $command->callAction('handle', $parameters);
			}
		}
		//
		return false;
	}

	/**
	 *	Builds a new instance of the dispatcher
	 *
	 *	@param	\Collei\Console\ConsoleApp	$app	
	 */
	public function __construct(ConsoleApp $app)
	{
		$this->app = $app;
	}

	/**
	 *	Dispatches the call to the console servlet
	 *
	 *	@param	\Collei\Console\Commands\Command $command	
	 *	@param	\Collei\Console\CommandLine	$commandLine
	 *	@return	mixed
	 */
	public function dispatch(Command $command, CommandLine $commandLine)
	{
		return $this->callInstance($command, $commandLine);
	}

}

