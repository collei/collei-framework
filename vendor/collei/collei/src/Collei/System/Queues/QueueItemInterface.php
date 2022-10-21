<?php
namespace Collei\System\Queues;

/**
 *	Queue item contract
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-17
 */
interface QueueItemInterface
{
	/**
	 *	Calls the main code to be run by this item.
	 *
	 *	@return	void
	 */
	public function run();
}


