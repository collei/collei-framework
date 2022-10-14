<?php
namespace Collei\System\Sockets;

use Collei\System\Sockets\AbstractSocket;
use Collei\System\Sockets\AcceptedSocket;
use DateTime;

/**
 *	Encapsulates a socket
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-13
 */
class Socket extends AbstractSocket
{
	/**
	 *	@var bool $bound
	 */
	private $bound = false;
	
	/**
	 *	@var array $bindInfo
	 */
	private $bindInfo = [];
	
	/**
	 *	@var bool $connected
	 */
	private $connected = false;
	
	/**
	 *	@var array $connectionInfo
	 */
	private $connectionInfo = [];
	
	/**
	 *	Initializes a socket. Same parameters as of socket_create().
	 *
	 *	@param	int		$domain
	 *	@param	int		$type
	 *	@param	int		$protocol
	 *	@return	void
	 */
	public function __construct(int $domain, int $type, int $protocol)
	{
		parent::__construct();
		//
		if (!function_exists('socket_create')) {
			throw new SocketException(
				"Socket extension not installed on PHP.", -1
			);
		}
		//
		if ($sock = @socket_create($domain, $type, $protocol)) {
			$this->setSock($sock);
		} else {
			$this->logError(socket_last_error(), [
				'description' => 'Could not create socket for.',
				'domain' => $domain,
				'type' => $type,
				'protocol' => $protocol,
			]);
		}
	}

	/**
	 *	Initializes a socket. Same parameters as of socket_create().
	 *
	 *	@param	int		$domain
	 *	@param	int		$type
	 *	@param	int		$protocol
	 *	@return	this
	 */
	public function bind(string $address, int $port = 0)
	{
		if ($this->bound) {
			$this->logError(-1, [
				'reason' => 'Socket already in use.',
				'description' => 'Socket already bound to.',
				'address' => $address,
				'port' => $port
			]);
		} else {
			$this->bound = @socket_bind($this->getSock(), $address, $port);
			$this->bindInfo = [
				'address' => $address,
				'port' => $port,
				'timestamp' => new DateTime()
			];
			//
			if (!$this->bound) {
				$this->logError(socket_last_error(), [
					'description' => 'Could not bind socket to.',
					'address' => $address,
					'port' => $port,
				]);
			}
		}
		//
		return $this;
	}

	/**
	 *	Connects it. Same parameters as of socket_connect() and
	 *	it will call socket_bind() if needed.
	 *	Parameters are optional if method bind() was previously called;
	 *	otherwise you MUST provide them. 
	 *
	 *	@param	string	$address = null
	 *	@param	int		$port = null
	 *	@return	this
	 */
	public function connect(string $address = null, int $port = null)
	{
		if ($this->connected) {
			return $this;
		}
		//
		if (!$this->bound) {
			if (is_null($address) || is_null($port)) {
				$this->logError(-1, [
					'reason' => 'Empty or invalid address/port',
					'description' => (
						'Provide address/port info or call the method bind() '
						. 'before connect()\'ing.'
					),
					'address' => $address,
					'port' => $port
				]);
			} else {
				$this->bind($address, $port);
			}
		}
		//
		if ($this->bound) {
			$this->connectionInfo = [
				'address' => $address,
				'port' => $port,
				'timestamp' => new DateTime()
			];
			$this->connected = socket_connect($this->getSock(), $address, $port);
		}
		//
		if (!$this->connected) {
			$this->logError(socket_last_error(), [
				'description' => 'Could not connect to.',
				'address' => (
					$this->connectionInfo['address'] ?? $address ?? '(none)'
				),
				'port' => (
					$this->connectionInfo['port'] ?? $port ?? '(none)'
				),
			]);
		}
		//
		return $this;
	}

	/**
	 *	Starts listening for connections, setting maximum count of allowed
	 *	connections on queue to $maxConnections.
	 *
	 *	@param	int		$maxConnections = 0
	 *	@return	this
	 */
	public function listen(int $maxConnections = 0)
	{
		if ($this->bound) {
			socket_listen($this->getSock(), $maxConnections);
		} else {
			$this->logError(-1, [
				'reason' => 'Socket must be bind before listen',
				'description' => 'Call method bind() before listen()\'ing.'
			]);
		}
		//
		return $this;
	}

	/**
	 *	Starts listening for connections, setting maximum count of allowed
	 *	connections on queue to $maxConnections.
	 *
	 *	@return	\Collei\System\Sockets\ClientSocket|null
	 */
	public function accept()
	{
		$clientSock = socket_accept($this->getSock());
		//
		if (false !== $clientSock) {
			return ClientSocket::makeClient($clientSock);
		}
		//
		return null;
	}




}


