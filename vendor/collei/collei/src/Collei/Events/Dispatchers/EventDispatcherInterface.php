<?php
namespace Collei\Events\Dispatchers;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Defines a dispatcher for events
 */
interface EventDispatcherInterface
{
	/**
	 *	Provide all relevant listeners with an event to process.
	 *
	 *	@param	object	$event
	 *	@return	object
	 */
	public function dispatch(object $event): object;
}

