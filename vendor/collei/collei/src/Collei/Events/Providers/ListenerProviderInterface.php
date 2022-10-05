<?php
namespace Collei\Events\Providers;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Mapper from an event to the proper listeners for that event
 */
interface ListenerProviderInterface
{
	/**
	 *	Returns a bunch of listeners for that event.
	 *
	 *	@param	object	$event
	 *	@return	iterable<callable>
	 */
	public function getListenersForEvent(object $event): iterable;
}

