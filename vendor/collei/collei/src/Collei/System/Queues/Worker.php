<?php
namespace Collei\System\Queues;

use Collei\System\Queues\Queue;
use Collei\System\Queues\QueueInterface;
use Collei\System\Sockets\Socket;

/**
 *	Worker implementation of QueueControllerInterface
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-18
 */
class Worker implements QueueControllerInterface
{
	/**
	 *	@static
	 *	@var int $defaultPort
	 */
	private static $defaultPort = 10952;

	/**
	 *	@static
	 *	@var int $initialAddress
	 */
	private static $initialAddress = 0x7F000040;

	/**
	 *	Generates a new Local IP Address of form 127.x.y.z
	 *	with both x and y between 0 and 255 (inclusive) and
	 *	z between 1 and 254 (inclusive).
	 *
	 *	@return	string
	 */
	private static function generateLocalAddress()
	{
		do {
			$next = ++self::$initialAddress;
		} while (($next & 0x000000FF) == 0 || ($next & 0x000000FF) == 255);
		//
		return implode('.', [
			($next & 0x7F000000) >> 24,
			($next & 0x00FF0000) >> 16,
			($next & 0x0000FF00) >> 8,
			($next & 0x000000FF)
		]);
	}

	/**
	 *	Change the default listen port for further Workers.
	 *	Returns the current port set on success.
	 *	If $port <= 9001 or $port >= 65536, then fails, keeps the
	 *	default unchanged and returns false.
	 *
	 *	@static
	 *	@param	int	$port
	 *	@return	int|false
	 */
	public static function setDefaultPort(int $port)
	{
		if (($port > 9001) && ($port < 65536)) {
			$current = self::$defaultPort;
			self::$defaultPort = $port;
			//
			return $current;
		}
		//
		return false;
	}

	/**
	 *	@var \Collei\System\Queues\QueueInterface $queue
	 */
	private $queue;

	/**
	 *	@var array $queueItems
	 */
	private $queueItems = [];

	/**
	 *	@var \Collei\System\Sockets\Socket $connection
	 */
	private $connection;

	/**
	 *	@var string $address
	 */
	private $address = '';

	/**
	 *	@var int $port
	 */
	private $port;

	/**
	 *	Processes input data from socket clients.
	 *
	 *	@return	void
	 */
	public function __construct()
	{
		$this->address = self::generateLocalAddress();
		$this->port = self::$defaultPort;
		//
		$this->connection = (new Socket(AF_INET, SOCK_STREAM, SOL_TCP))
			->setOption(SOL_SOCKET, SO_REUSEADDR, 1)
			->bind($this->address, $this->port)
			->listen();
	}

	/**
	 *	Processes input data from socket clients.
	 *
	 *	@return	void
	 */
	protected function processData(string $data)
	{
		echo "\r\n[socket] $data";
	}

	/**
	 *	Starts the worker main loop.
	 *
	 *	@return	void
	 */
	public function watch()
	{
		$clients = $this->connection->asSocketArray();
		$sock = $clients[0];
		//
		while (true) {
			// copy it to preserve the original
			$read = $clients;
			//
			// List clients with available data; if none, go next iteration. 
			if (
				socket_select($read, $write = NULL, $except = NULL, NULL) < 1
			) {
				continue;
			}
			//
			// if some client trying to connect...
			if (in_array($sock, $read)) {
				// accepts and enlists it and 
				$clients[] = $newsock = socket_accept($sock);
				//
				// removes the listening socket from the clients array
				$key = array_search($sock, $read);
				unset($read[$key]);
			}
			//
			// loop through clients so we can read them
			foreach ($read as $read_sock) {
				//
				$content = '';
				$bytes = 0;
				//
				while (true) {
					$bytes = @socket_recv($read_sock, $data, 1024, 0);
					$content .= trim($data);
					//
					if ((0 === $bytes) || (false === $bytes)) {
						break;
					}
				}
				//
				if (!empty($content)) {
					$this->processData($content);
				}
			}
		}
		//
		$this->connection->close();
	}





	/**
	 *	Entry point of the queue controller.
	 *	It will be called at least once for each queue item.
	 *
	 *	@param	\Collei\System\Queues\QueueInterface	$queue
	 *	@return	void
	 */
	public function __invoke(QueueInterface $queue)
	{

	}

}


