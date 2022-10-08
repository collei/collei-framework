<?php
namespace App\Listeners;

use Collei\App\Events\AfterStartEvent;
use Collei\App\App;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-06
 *
 *	Mapper from an event to the proper listeners for that event
 */
class AfterStartListener
{

	public function __invoke(AfterStartEvent $event)
	{
		$object = $event->getContext();

		if ($object instanceof App) {
			logit(__METHOD__, "Called your SECOND listener, sir ^^");
		}
	}

}
