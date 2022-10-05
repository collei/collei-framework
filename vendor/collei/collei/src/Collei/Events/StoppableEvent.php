<?php
namespace Collei\Events;

use Collei\Events\Event;
use Collei\Events\Dispatchers\StoppableEventTrait;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	Parent class of all stoppable events.
 */
class StoppableEvent extends Event implements StoppableEventInterface
{
	use StoppableEventTrait;
}

