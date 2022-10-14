<?php
namespace Collei\System\Sockets;

use Collei\System\Sockets\SocketException;
use Collei\System\Sockets\ClientSocket;
use Collei\Utils\Logging\LoggerTrait;
use DateTime;

/**
 *	Encapsulates a socket
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-13
 */
abstract class AbstractSocket
{
	use LoggerTrait;

	/**
	 *	@static
	 *	@var bool $commonExceptionMode
	 */
	private static $commonExceptionMode = true;

	/**
	 *	Sets error handling option for further Sockets. It does not affect
	 *	those opened before adjustement. Returns the previous value.
	 *
	 *	@param	bool	$value
	 *	@return	bool
	 */
	public static function setExceptionMode(bool $value)
	{
		$previous = self::$commonExceptionMode;
		self::$commonExceptionMode = $value;
		//
		return $previous;
	}

	/**
	 *	@var \Socket|resource $sock
	 */
	private $sock;

	/**
	 *	Initializes a ClientSocket with a PHP socket resource.
	 *
	 *	@param	int		$errNumber
	 *	@param	array	$details = null
	 *	@return	void
	 */
	protected static final function makeClient($clientSocket)
	{
		$client = new ClientSocket();
		$client->setSock($clientSocket);
		//
		return $client;
	}
	
	/**
	 *	@var bool $blockingMode
	 */
	private $blockingMode = false;
	
	/**
	 *	@var bool $exceptionMode
	 */
	private $exceptionMode = true;

	/**
	 *	Logs socket errors.
	 *
	 *	@param	int		$errNumber
	 *	@param	array	$details = null
	 *	@return	void
	 */
	protected function logError(int $errNumber, array $details = null)
	{
		$errReason = socket_strerror($errNumber);
		//
		if (-1 === $errNumber) {
			$errReason = $details['reason']
				?? $details['description']
				?? '';
			$errReason .= (!empty($errReason))
				? (': ' . ($details['description'] ?? ''))
				: ($details['description'] ?? 'Unknown reason.');
			//
			if (': ' === substr($errReason, -2)) {
				$errReason = substr($errReason, 0, -2);
			}
		}
		//
		if (is_empty($details)) {
			$details = [
				'error' => "$errNumber $errReason."
			];
		} else {
			$details['error'] = "$errNumber $errReason.";
			$errReason .= ". \r\nDetails: " . print_r($details, true);
		}
		//
		socket_clear_error();
		//
		if ($this->exceptionMode) {
			throw new SocketException(
				"Socket error $errNumber: $errReason.", $errNumber
			);
		} else {
			$this->log($errNumber, 'socket error', $errReason);
		}
	}

	/**
	 *	Returns the underlying Socket.
	 *
	 *	@return	\Socket|resource
	 */
	protected function getSock()
	{
		return $this->sock;
	}

	/**
	 *	Returns the underlying Socket.
	 *
	 *	@return	\Socket|resource
	 */
	protected function setSock($sock)
	{
		$this->sock = $sock;
	}

	/**
	 *	Initializes the AbstractSocket.
	 *
	 *	@return	\Socket|resource
	 */
	protected function __construct()
	{
		$this->exceptionMode = self::$commonExceptionMode;
	}

	/**
	 *	Terminates and closes the uynderlying socket.
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		$linger = array(
			'l_linger' => 0,
			'l_onoff' => 1
		);
		//
		socket_set_option($this->sock, SOL_SOCKET, SO_LINGER, $linger);
		socket_close($this->sock);
	}

	/**
	 *	Sets blocking mode.
	 *
	 *	@return	this
	 */
	public function setBlock()
	{
		if (!socket_set_block($this->sock)) {
			$this->logError(socket_last_error(), [
				'description' => 'Could not set block mode for socket.',
				'socket' => $this->sock
			]);
		} else {
			$this->blockingMode = true;
		}
		//
		return $this;
	}

	/**
	 *	Sets non-blocking mode.
	 *
	 *	@return	this
	 */
	public function setNonBlock()
	{
		if (!socket_set_nonblock($this->sock)) {
			$this->logError(socket_last_error(), [
				'description' => 'Could not set nonblock mode for socket.',
				'socket' => $this->sock
			]);
		} else {
			$this->blockingMode = false;
		}
		//
		return $this;
	}

	/**
	 *	Mirrors socket_recv().
	 *
	 *	@param	string	&$data
	 *	@param	int		$length
	 *	@param	int		$falgs
	 *	@return	int|false
	 */
	public function recv(string &$data, int $length, int $flags)
	{
		return socket_recv($this->sock, $data, $length, $flags);
	}

	/**
	 *	Reads data on binary, non-blocking mode.
	 *	Returns number of bytes read on success, false otherwise.
	 *	Error is logged or an Exception is raised, according to settings.
	 *
	 *	@param	string	&$content
	 *	@return	int|false
	 */
	public function read(string &$content)
	{
		$content = '';
		$totalRead = 0;
		//
		while (true) {
			$data = '';
			$bytes = @socket_recv($this->sock, $data, 1024, 0);
			//
			if (false === $bytes) {
				$this->logErrorSilently(socket_last_error(), [
					'description' => 'Could not read from socket.',
					'socket' => $this->sock
				]);
				//
				return false;
			} else {
				$totalRead += $bytes;
				$content .= rtrim($data);
				//
				if (0 === $bytes) {
					break;
				}
			}
		}
		//
		return $totalRead;
	}

	/**
	 *	Mirrors socket_send().
	 *
	 *	@param	string	$data
	 *	@param	int		$length
	 *	@param	int		$falgs
	 *	@return	int|false
	 */
	public function send(string $data, int $length, int $flags)
	{
		return socket_send($this->sock, $data, $length, $flags);
	}

	/**
	 *	Writes data to the socket.
	 *	Returns true on success, false otherwise.
	 *	Error is logged or an Exception is raised, according to settings.
	 *
	 *	@param	string	&$content
	 *	@return	int|false
	 */
	public function write(string $content)
	{
		$totalSent = 0;
		$length = strlen($content);
		$nllength = strlen("\r\n");
		//
		while (true) {
			$sent = @socket_send(
				$this->sock, "$content\r\n", ($length + $nllength), 0
			);
			//
			if ($sent === false) {
				$this->logError(socket_last_error(), [
					'description' => 'Could not write to socket.',
					'socket' => $this->sock
				]);
				//
				return false;
			}
			// Check if the entire message has been sented
			if ($sent < $length) {
				// If not sent the entire message.
				// Get the part of the message that has not yet been sented as message
				$content = substr($content, $sent);
				// Get the length of the not sented part
				$length -= $sent;
				// Calculates the total bytes sent
				$totalSent += $sent;
			} else {
				break;
			}
		}
		//
		return $totalSent;
	}


}


