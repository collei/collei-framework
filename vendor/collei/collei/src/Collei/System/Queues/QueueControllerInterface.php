<?php
namespace Collei\System\Queues;

use Collei\System\Queues\QueueInterface;

/**
 *	QueueControllerInterface contract
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-17
 */
interface QueueControllerInterface
{
	/**
	 *	Entry point of the queue controller.
	 *	It will be called at least once for each queue item.
	 *
	 *	@param	\Collei\System\Queues\QueueInterface	$queue
	 *	@return	void
	 */
	public function __invoke(QueueInterface $queue);
}


