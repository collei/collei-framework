<?php
namespace Collei\Events\Dispatchers;

use Collei\Events\EventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	An event whose processing may be interrupted when the event
 *	has been handled.
 *	A Dispatcher implementation MUST check to determine if an Event
 *	is marked as stopped after each listener is called.
 *	If it is, then it should return immediatelly without calling any
 *	further Listeners.
 */
interface StoppableEventInterface extends EventInterface
{
	/**
	 *	Used by the Dispatcher to determine if previous listener halted
	 *	propagation of such event.
	 *
	 *	@return	bool
	 */
	public function isPropagationStopped(): bool;
}

