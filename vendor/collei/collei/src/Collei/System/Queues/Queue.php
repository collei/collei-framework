<?php
namespace Collei\System\Queues;

use Collei\System\Queues\QueueInterface;
use Collei\System\Queues\QueueItemInterface;
use Collei\System\Queues\QueueControllerInterface;

/**
 *	Queue base implementation
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-18
 */
class Queue implements QueueInterface
{
	/**
	 *	@var array $queue
	 */
	private $queue = [];

	/**
	 *	@var \Collei\System\Queues\QueueControllerInterface $controller
	 */
	private $controller = null;

	/**
	 *	@var bool $paused
	 */
	private $paused = false;

	/**
	 *	@var bool $stopped
	 */
	private $stopped = false;

	/**
	 *	Adds an QueueItemInterface object.
	 *
	 *	@param	\Collei\System\Queues\QueueItemInterface	$queueItem
	 *	@return	void
	 */
	public function addItem(QueueItemInterface $queueItem)
	{
		$this->queue[] = array(
			'item' => $queueItem,
			'delay' => 0
		);
	}

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
	) {
		$this->queue[] = array(
			'item' => $queueItem,
			'delay' => $seconds
		);
	}

	/**
	 *	Remove a previously added QueueItemInterface object.
	 *
	 *	@param	\Collei\System\Queues\QueueItemInterface	$queueItem
	 *	@return	void
	 */
	public function removeItem(QueueItemInterface $queueItem)
	{
		$found = false;
		//
		foreach ($this->queue as $key => $item) {
			if ($queueItem === $item['item']) {
				$found = $key;
				break;
			}
		}
		//
		if ($found) {
			unset($this->queue[$found]);
		}
	}

	/**
	 *	Pauses the queue.
	 *
	 *	@return	void
	 */
	public function pause()
	{
		$this->paused = true;
	}

	/**
	 *	Resumes the queue.
	 *
	 *	@return	void
	 */
	public function resume()
	{
		$this->paused = false;
	}

	/**
	 *	Stops the queue.
	 *
	 *	@return	void
	 */
	public function stop()
	{
		$this->stopped = true;
	}

	/**
	 *	Returns if the Queue is currently paused.
	 *
	 *	@return	bool
	 */
	public function isPaused(): bool
	{
		return $this->paused;
	}

	/**
	 *	Returns if the Queue is currently paused.
	 *
	 *	@return	bool
	 */
	public function isStopped(): bool
	{
		return $this->stopped;
	}

	/**
	 *	Attach a QueueControllerInterface to the queue controller.
	 *	It will be called at least once right before any delay, which it
	 *	may happen right before an item execution.
	 *	It must be able to receive the instance of QueueInterface
	 *	in order to control it.
	 *
	 *	@return	void
	 */
	public function attachController(QueueControllerInterface $controller)
	{
		$this->controller = $controller;
	}

	/**
	 *	Starts the call-chain of queue items.
	 *	MUST return the last ran item, UNLESS an error prevented the queue
	 *	from being started right before calling run() on the FIRST item.
	 *
	 *	@return	\Collei\System\Queues\QueueItemInterface
	 */
	public function start(): QueueItemInterface
	{
		return $this->runQueue();
	}

	/**
	 *	A service method. It runs the queue.
	 * 
	 *	@return	\Collei\System\Queues\QueueItemInterface
	 */
	protected function runQueue(): QueueItemInterface
	{
		foreach ($this->queue as $item) {
			// checks for stopped state
			if ($this->isStopped()) {
				break;
			}
			//
			// it will be run at least once
			do {
				// allows for some control
				$this->control();
				// checks for paused state
			} while ($this->isPaused());
			//
			// if delay is set (delay > 0)...
			if ($item['delay'] > 0) {
				// delays for some time,
				sleep($item['delay']);
			}
			//
			// now runs it
			$item['item']->run();
		}
		//
		return $item['item'] ?? null;
	}

	/**
	 *	A service method. It assists on queue running
	 *	by keeping a communication channel through a local socket.
	 * 
	 *	@return	void
	 */
	protected function control()
	{
		if (is_null($this->controller)) {
			return;
		}
		//
		$clos = $this->controller;
		$clos($this);
	}

}


