<?php
namespace Collei\System\Sockets;

use Collei\System\Sockets\Socket;

/**
 *	Encapsulates a client socket
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-13
 */
class TinyServerSocket extends Socket
{
	/**
	 *	Calls a callable (maybe a regular function name, a Closure or
	 *	even a whole __invoke()'able class). It MUST be able to receive
	 *	one object parameter (preferably a \Collei\System\Sockets\ClientSocket)
	 *	and MUST return a bool value (true to remain in the loop,
	 *	false otherwise).
	 *
	 *	@param	callable	$listener
	 *	@param	int			$limit = 0
	 *	@return	void
	 */
	public function loop(callable $listener, int $limit = null)
	{
		if (!$this->isBound()) {
			$this->logError(new SocketListenerException(
				'Cannot listen to this socket now.', -1
			));
		}
		//
		$this->listen($limit);
		//
		while (true) {
			if ($client = $this->accept()) {
				$remains = $listener($client);
				//
				if ($remains === true) {
					break;
				}
			} else {
				break;
			}
		}
	}

}

