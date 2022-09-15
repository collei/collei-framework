<?php 
namespace Collei\Servlets;

use Collei\Pacts\Makeable;

/**
 *	Basic servlet structure
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-05-xx
 */
abstract class Servlet implements Makeable
{

	/**
	 *	Performs authorization depending on the current user
	 *
	 *	@return bool
	 */
	protected function authorize()
	{
		return true;
	}

	/**
	 *	Performs initialization tasks
	 *
	 *	@return void
	 */
	protected function init()
	{
		// does nothing here, but it exists to be overriden by subclasses
	} 

	/**
	 *	Performs finalization tasks
	 *
	 *	@return void
	 */
	protected function term()
	{
		// does nothing here, but it exists to be overriden by subclasses
	}

	/**
	 *	Performs needed tasks before calling 
	 *
	 *	@return void
	 */
	protected function before()
	{
		// does nothing here, but it exists to be overriden by subclasses
	}

	/**
	 *	Performs needed tasks after calling 
	 *
	 *	@return void
	 */
	protected function after()
	{
		// does nothing here, but it exists to be overriden by subclasses
	}

	/**
	 *	Instantiates a new Servlet instance
	 *
	 */
	public function __construct()
	{
		$this->init();
	}

	/**
	 *	Finalizes the Servlet instance
	 *
	 *	@return void
	 */
	public function __destruct()
	{
		$this->term();
	}

	/**
	 *	Calls the specified method with the parameters set
	 *
	 *	@param	string	$method
	 *	@param	array	$parameters
	 *	@return mixed
	 */
	public function callAction(string $method, $parameters)
	{
		$this->before();
		$result = call_user_func_array([$this, $method], $parameters);
		$this->after();
		//
		return $result;
	}

	/**
	 *	Creates a new instance of the class. 
	 *
	 *	@static
	 *	@return	instanceof \Collei\Servlets\Servlet
	 */
	public static function make()
	{
		return new static();
	}

}

