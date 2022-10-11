<?php
namespace Collei\Events\Dispatchers;

use Collei\Events\Providers\ListenerProviderInterface;
use Collei\Events\Dispatchers\EventDispatcherInterface;
use Collei\Events\Dispatchers\StoppableEventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Just a standard EventDispatcher.
 */
class EventDispatcher implements EventDispatcherInterface
{
	/**
	 *	@var \Collei\Events\Dispatchers\ListenerProviderInterface $provider
	 */
	private $provider;

	/**
	 *	Constructor.
	 *
	 *	@param	\Collei\Events\Dispatchers\ListenerProviderInterface	$provider
	 */
	public function __construct(ListenerProviderInterface $provider)
	{
		$this->provider = $provider;
	}

	/**
	 *	Provide all relevant listeners with an event to process.
	 *
	 *	@param	object	$event
	 *	@return	object
	 */
	public function dispatch(object $event): object
	{
		if ($event instanceof StoppableEventInterface) {
			if ($event->isPropagationStopped()) {
				return $event;
			}
		}
		//
		foreach ($this->provider->getListenersForEvent($event) as $listener) {
			$listener($event);
		}
		//
		return $event;
	}

	/**
	 *	Builds with the Provider in and returns it.
	 *
	 *	@param	object	$event
	 *	@return	self
	 */
	public static function to(ListenerProviderInterface $provider)
	{
		return new self($provider);
	}


}

