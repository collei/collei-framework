<?php
namespace Collei\Events;

use Collei\Events\DataEvent;
use Collei\Events\Dispatchers\StoppableEventInterface;

/**
 *	@author	Alarido	<alarido.su@gmail.com>
 *	@author	Collei Inc. <collei@collei.com.br>
 *	@since	2022-10-04
 *
 *	An stoppable DataEvent.
 */
class StoppableDataEvent extends DataEvent implements StoppableEventInterface
{
	use StoppableEventTrait;
}

