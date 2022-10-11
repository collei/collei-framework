<?php
namespace App\Listeners;

use App\Events\ContactAddEvent;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-06
 *
 *	Mapper from an event to the proper listeners for that event
 */
class ContactAddListener
{
	public function __invoke(ContactAddEvent $event)
	{
		/**
		 * @todo your relevant code here
		 */
		logit(
			__METHOD__,
			'Added contact: ' . print_r($event->getContact(), true)
		);
	}

}
