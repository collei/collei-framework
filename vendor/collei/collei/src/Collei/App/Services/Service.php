<?php
namespace Collei\App\Services;

use Collei\Contracts\Makeable;

/**
 *	Basement of all service classes
 *
 *	@author	alarido <alarido.su@gmail.com>
 *	@since	2021-07-xx
 */
class Service implements Makeable 
{
	/**
	 *	Builds and initializes a new instance of the Service
	 *
	 */
	private function __construct()
	{
		$this->init();
	}

	/**
	 *	Terminates this instance of the Service
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		$this->finish();
	}

	/**
	 *	Service factory. Creates a new instance of the class. 
	 *
	 *	@static
	 *	@param	mixed	...$args
	 *	@return	instanceof \Collei\Services\Service
	 */
	public static function make(...$args)
	{
		return new static();
	}

	/**
	 *	Performs any needed initialization right after construct
	 *
	 *	@return	void
	 */
	protected function init()
	{
	}

	/**
	 *	Performs any needed termination right before destory
	 *
	 *	@return	void
	 */
	protected function finish()
	{
	}

}


