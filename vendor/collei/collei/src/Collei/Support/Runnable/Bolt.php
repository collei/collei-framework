<?php
namespace Collei\Support\Runnable;

use Closure;
use Collei\Support\Runnable\Runnable;

/**
 *	A single runnable object with attached parameters
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-10-12
 */
abstract class Bolt implements Runnable
{
	/**
	 *	@var array $arguments
	 */
	private $arguments = array();

	/**
	 *	Executes the defined procedure
	 *
	 *	@return	void
	 */
	public function run()
	{
		return;
	}

	/**
	 *	Defines a parameter
	 *
	 *	@param	mixed	$name
	 *	@param	mixed	$value
	 *	@return	void
	 */
	public function __set($name, $value)
	{
		$this->arguments[$name] = $value;
	}

	/**
	 *	Retrieves any of set parameters
	 *
	 *	@param	mixed	$name
	 *	@return	mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->arguments))
		{
			return $this->arguments[$name];
		}
		return '';
	}

	/**
	 *	Performs single execution of the specified closure,
	 *	returning its result.
	 *
	 *	@param	\Closure	$closure
	 *	@return	mixed
	 */
	public static function returnFrom(Closure $closure)
	{
		if (!is_null($closure))
		{
			return $closure();
		}
		return null;
	}

}


