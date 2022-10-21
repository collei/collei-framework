<?php
namespace Collei\System\Queues;

use Collei\System\Queues\QueueItemInterface;
use DateTime;

/**
 *	Queue contract
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-17
 */
interface QueueInterface
{
	/**
	 *	Adds an QueueItemInterface object.
	 *
	 *	@param	\Collei\System\Queues\QueueItemInterface	$queueItem
	 *	@return	void
	 */
	public function addItem(QueueItemInterface $queueItem);

	/**
	 *	Adds an QueueItemInterface object with a delay in seconds
	 *	relative to the last added one.
	 *
	 *	@param	\Collei\System\Queues\QueueItemInterface	$queueItem
	 *	@param	int		$seconds
	 *	@return	void
	 */
	public function addDelayedItem(
		QueueItemInterface $queueItem, int $seconds
	);

	/**
	 *	Remove a previously added QueueItemInterface object.
	 *
	 *	@param	\Collei\System\Queues\QueueItemInterface	$queueItem
	 *	@return	void
	 */
	public function removeItem(QueueItemInterface $queueItem);

	/**
	 *	Pauses the queue.
	 *
	 *	@return	void
	 */
	public function pause();

	/**
	 *	Resumes the queue from the paused point.
	 *
	 *	@return	void
	 */
	public function resume();

	/**
	 *	Stops the queue.
	 *
	 *	@return	void
	 */
	public function stop();

	/**
	 *	Returns if the Queue is currently paused.
	 *
	 *	@return	bool
	 */
	public function isPaused(): bool;

	/**
	 *	Returns if the Queue is currently paused.
	 *
	 *	@return	bool
	 */
	public function isStopped(): bool;

	/**
	 *	Attach a callable to the queue controller. Such callable
	 *	will be called at least once right before any delay, which it
	 *	may happen right before an item execution.
	 *	Such callable must be able to receive the instance of
	 *	QueueInterface in order to control it.
	 *
	 *	@return	void
	 */
	public function attachController(callable $closure);

	/**
	 *	Starts the call-chain of queue items.
	 *	MUST return the last ran item, UNLESS an error prevented the queue
	 *	from being started right before calling run() on the FIRST item.
	 *
	 *	@return	\Collei\System\Queues\QueueItemInterface
	 */
	public function start(): QueueItemInterface;
}


