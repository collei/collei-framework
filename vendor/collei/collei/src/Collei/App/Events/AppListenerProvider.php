<?php
namespace Collei\App\Events;

use Collei\Events\Providers\ListenerProvider;
use Collei\Events\Providers\ListenerProviderInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Mapper from an event to the proper listeners for that event
 */
class AppListenerProvider extends ListenerProvider implements ListenerProviderInterface
{
	/**
	 *	@var self $instance
	 */
	private static $instance = null;

	/**
	 *	Returns a bunch of listeners for that event.
	 *
	 *	@param	object	$event
	 *	@return	iterable<callable>
	 */
	public static function getInstance(): self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		//
		return self::$instance;
	}
}

