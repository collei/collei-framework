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
	 *	Extracts reason from provided details, if any.
	 *
	 *	@static
	 *	@param	array	$details = null
	 *	@param	string	$alternative = null
	 *	@return	string
	 */
	private static function reasonFromDetails(
		int $errorNumber, array $details = null, string $alternative = null
	) {
		$reason = 'Unknown reason';
		//
		if (0 > $errorNumber) {
			$reason = $details['reason'] ?? $details['description'] ?? '';
			$reason .= (!empty($reason))
				? (': ' . ($details['description'] ?? ''))
				: ($details['description'] ?? $alternative ?? 'Unknown reason');
			//
			if (': ' === substr($reason, -2)) {
				$reason = substr($reason, 0, -2);
			}
		} else {
			$reason = socket_strerror($errorNumber);
		}
		//
		socket_clear_error();
		//
		return $reason;
	}

	/**
	 *	Logs socket errors.
	 *
	 *	@param	int		$errNumber
	 *	@param	array	$details = null
	 *	@return	void
	 */
	protected function logErrorSilently(int $errNumber, array $details = null)
	{
		$errReason = self::reasonFromDetails(
			$errNumber, $details, 'Unknown reason'
		);
		//
		if (empty($details)) {
			$details = array('error' => "$errNumber $errReason.");
		} else {
			$details['error'] = "$errNumber $errReason.";
		}
		//
		$errReason .= ". \r\nDetails: " . print_r($details, true);
		//
		$this->log(
			$errNumber, 'socket error', "Socket error $errNumber: $errReason."
		);
	}
	
	/**
	 *	Logs socket errors OR raises an Exception.
	 *
	 *	@param	int		$errNumber
	 *	@param	array	$details = null
	 *	@return	void
	 */
	protected function logError(
		SocketException $exception, array $details = null
	) {
		$code = $exception->getCode();
		$message = $exception->getMessage();
		//
		if ($this->exceptionMode) {
			$reason = ($code < 0) ? '' : socket_strerror($code);
			//
			if (!empty($details)) {
				$message .= (" \r\nDetails: " . print_r($details, true));
			}
			//
			throw new SocketException(
				"Socket error $code: $reason.", $code, $exception
			);
		} else {
			$this->logErrorSilently($code, $details);
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
			$this->logError(new SocketException(
					'Could not set block mode for socket.',
					socket_last_error()
				), [
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
			$this->logError(new SocketException(
					'Could not set nonblock mode for socket.',
					socket_last_error()
				), [
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
				$this->logError(new SocketReadException(
						'Could not read from socket.',
						socket_last_error()
					), [
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
				$this->logError(new SocketWriteException(
						'Could not write to socket.',
						socket_last_error()
					), [
						'socket' => $this->sock
				]);
				//
				return false;
			}
			// Check if the entire message has been sent
			if ($sent < $length) {
				// If not sent the entire message.
				// Get the part of the message that has not yet been sent as message
				$content = substr($content, $sent);
				// Get the length of the not sent part
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


