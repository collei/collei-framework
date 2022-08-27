<?php
namespace Collei\Utils\Values;

use InvalidArgumentException;

/**
 *	Embodies tasks on byte stream
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-08-03
 */
class Stream
{
	/**
	 *	@var mixed $bytes
	 */
	private $bytes = [];

	/**
	 *	@var int $length
	 */
	private $length = 0;

	/**
	 *	@var int $currentPos
	 */
	private $currentPos = 0;

	/**
	 *	Instantiates a Stream
	 *
	 *	@param	string|array	$bytes
	 */
	public function __construct($bytes)
	{
		if (\is_string($bytes)) {
			$this->bytes = \str_split($bytes, 1);
			$this->length = \strlen($bytes);
		} elseif (\is_array($bytes)) {
			$this->bytes = $bytes;
			$this->length = \count($bytes);
		} else {
			throw new InvalidArgumentException(
				"Stream must be initialized with a string or an array of bytes"
			);
		}
	}

	/**
	 *	Reads $count bytes from the stream
	 *
	 *	@param	int	$count
	 *	@return	array
	 */
	public function read(int $count, int $readCount = null)
	{
		$read = [];
		$readCount = 0;
		$max = $this->currentPos + $count;
		if ($max > $this->length) {
			$max = $this->length;
		}
		//
		while ($this->currentPos < $max) {
			$read[] = $this->bytes[$this->currentPos++];
			$readCount++;
		}
		//
		return $read;
	}

	/**
	 *	Returns if the end of stream was reached or not
	 *
	 *	@return	bool
	 */
	public function eof()
	{
		return ($this->currentPos >= $this->length);
	}

}


