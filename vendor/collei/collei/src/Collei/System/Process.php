<?php
namespace Collei\System;

use Collei\Exceptions\ColleiException;

/**
 *	Encapsulates terminal basic features
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-07-08
 */
class Process
{
	/**
	 *	internal pipes for the child process
	 */
	public const STREAM_INPUT = 0;
	public const STREAM_OUTPUT = 1;
	public const STREAM_ERROR = 2;

	/**
	 *	@var string $command
	 */
	private $command;

	/**
	 *	@var array $pipes
	 */
	private $pipes;

	/**
	 *	@var string $hamdle
	 */
	private $handle;

	/**
	 *	@var array $descriptor
	 */
	private $descriptor = [
		self::STREAM_INPUT => ['pipe', 'r'],
		self::STREAM_OUTPUT => ['pipe', 'w'],
		self::STREAM_ERROR => ['pipe', 'w'],
	];

	/**
	 *	Starts the child process and initializes their pipes
	 *
	 *	@param	string	$command
	 *	@return	void
	 */
	private function init(string $command)
	{
		if (\is_resource($this->handle)) {
			return;
		}
		//
		$this->handle = \proc_open(
			$command,
			$this->descriptor,
			$this->pipes,
			null,
			null,
			['suppress_errors' => true]
		);
		//
		if (!\is_resource($this->handle)) {
			$this->handle = null;
		}
	}

	/**
	 *	Builds and initializes a process
	 *
	 *	@param	string	$command
	 *	@return	self
	 */
	public function __construct(string $command)
	{
		if (!\function_exists('proc_open')) {
			throw new ColleiException(
				'The function proc_open() is not available.'
			);
		}
		//
		$this->init(
			$this->command = $command
		);
	}

	/**
	 *	Finishes the child process and closes their pipes
	 *
	 *	@return	void
	 */
	public function __destruct()
	{
		if (!is_null($this->pipes)) {
			foreach ($this->pipes as $pipe) {
				\fclose($pipe);
			}
		}
		//
		if (is_resource($this->handle)) {
			\proc_close($this->handle);
		}
	}

	/**
	 *	Writes to the child process STDIN
	 *
	 *	@param	string	$str
	 *	@return	bool
	 */
	public function write(string $str)
	{
		if (!\is_resource($this->handle))
		{
			return null;
		}
		//
		return \fwrite(
			$this->pipes[self::STREAM_INPUT], $str
		) !== false;
	}

	/**
	 *	Reads $length bytes from the child process STDOUT.
	 *	If $length is omitted, reads all the currently available.
	 *
	 *	@param	int		$length = null
	 *	@return	string|false
	 */
	public function read(int $length = null)
	{
		if (!\is_resource($this->handle))
		{
			return null;
		}
		//
		if (!\is_null($length)) {
			return \stream_get_contents(
				$this->pipes[self::STREAM_OUTPUT], $length
			);
		}
		//
		return \stream_get_contents($this->pipes[self::STREAM_OUTPUT]);
	}

	/**
	 *	Reads all the currently available bytes
	 *	from the child process' STDERR.
	 *
	 *	@return	string|false
	 */
	public function readErrors()
	{
		if (!\is_resource($this->handle))
		{
			return null;
		}
		//
		return \stream_get_contents($this->pipes[self::STREAM_ERROR]);
	}

	/**
	 *	Does a quick read from a process.
	 *
	 *	@param	string	$command
	 *	@return	string|null
	 *	@throws	ColleiException
	 */
	private static function doQuickRead(string $command)
	{
		$proc = new self($command);
		$info = $proc->read();
		$proc = null;
		//
		return $info;
	}

	/**
	 *	Shortcut for starting a process, reading its output and closing it
	 *	in a single operation.
	 *
	 *	@param	string	$command
	 *	@param	bool	$quiet = false
	 *	@return	string|false
	 *	@throws	ColleiException
	 */
	public static function quickRead(string $command, bool $quiet = false)
	{
		$info = null;
		//
		if ($quiet) {
			try {
				$info = self::doQuickRead($command) ?? false;
			} catch (ColleiException $ce) {
				return false;
			}
		} else {
			$info = self::doQuickRead($command) ?? false;
		}
		//
		return $info ?? false;
	}

}

