<?php
namespace Collei\Events\Providers;

use Collei\Events\Providers\ListenerProviderInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Mapper from an event to the proper listeners for that event
 */
class ListenerProvider implements ListenerProviderInterface
{
	/**
	 *	@var array $listeners
	 */
	private $listeners = [];

	/**
	 *	Returns a bunch of listeners for that event.
	 *
	 *	@param	object	$event
	 *	@return	iterable<callable>
	 */
	public function getListenersForEvent(object $event): iterable
	{
		$eventType = get_class($event);
		//
		if (array_key_exists($eventType, $this->listeners)) {
			return $this->listeners[$eventType];
		}
		//
		return [];
	}

	/**
	 *	Register a Listener for the event.
	 *
	 *	@param	string		$eventType
	 *	@param	callable	$callable
	 *	@return	$this
	 */
	public function addListener(string $eventType, callable $callable): self
	{
		logit(__METHOD__, print_r([$eventType, $callable], true));

		$this->listeners[$eventType][] = $callable;
		//
		return $this;
	}

	/**
	 *	Clear all listeners of the given $eventType.
	 *
	 *	@param	string	$eventType
	 *	@return	void
	 */
	public function clearListeners(string $eventType): void
	{
		if (array_key_exists($eventType, $this->listeners)) {
			unset($this->listeners[$eventType]);
		}
	}

}

