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
	 *	Retrieves whether this socket is bound.
	 *
	 *	@return	bool
	 */
	public function isBound()
	{
		return $this->bound;
	}

	/**
	 *	Retrieves whether this socket is connected.
	 *
	 *	@return	bool
	 */
	public function isConnected()
	{
		return $this->connected;
	}

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
			$this->logError(new SocketException(
					'Could not create socket for.',
					socket_last_error()
				), [
					'domain' => $domain,
					'type' => $type,
					'protocol' => $protocol
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
			$this->logError(new SocketBindException(
					'Socket already in use',
					socket_last_error()
				), [
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
				$this->logError(new SocketBindException(
						'Could not bind socket to.',
						socket_last_error()
					), [
						'address' => $address,
						'port' => $port
				]);
			}
		}
		//
		return $this;
	}

	/**
	 *	Connects it. Same parameters as of socket_connect().
	 *
	 *	@param	string	$address
	 *	@param	int		$port = 0
	 *	@return	this
	 */
	public function connect(string $address, int $port = 0)
	{
		if ($this->connected) {
			return $this;
		}
		//
		$this->connectionInfo = [
			'address' => $address,
			'port' => $port,
			'timestamp' => new DateTime()
		];
		//
		$this->connected = @socket_connect($this->getSock(), $address, $port);
		//
		if (!$this->connected) {
			$this->logError(new SocketConnectionException(
					'Could not connect to.',
					socket_last_error()
				), [
					'address' => (
						$this->connectionInfo['address'] ?? $address ?? '(none)'
					),
					'port' => (
						$this->connectionInfo['port'] ?? $port ?? '(none)'
					)
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
	public function listen(int $maxConnections = null)
	{
		if ($this->isBound()) {
			if (!is_null($maxConnections)) {
				socket_listen($this->getSock(), $maxConnections);
			} else {
				socket_listen($this->getSock());
			}
		} else {
			$this->logError(
				new SocketConnectionException(
					'Could not listen to unbound. Please bind() it first!', -1
				)
			);
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


