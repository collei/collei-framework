<?php
namespace Collei\Support\Values;

use InvalidArgumentException;

/**
 *	Embodies tasks on byte stream
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-08-03
 */
class TextStream
{
	/**
	 *	@var string $bytes
	 */
	private $text = "";

	/**
	 *	@var int $length
	 */
	private $length = 0;

	/**
	 *	@var int $currentPos
	 */
	private $currentPos = 0;

	/**
	 *	Instantiates a TextStream object
	 *
	 *	@param	string	$text
	 */
	public function __construct(string $text)
	{
		$this->text = \str_replace(
			"\x01\x02\x03",
			PHP_EOL,
			\str_replace(
				[PHP_EOL, "\n", "\r", "\0"],
				["\x01\x02\x03", '', '', ''],
				$text
			)
		);
		//
		$this->length = \strlen($text);
	}

	/**
	 *	Reads $count characters from the text
	 *
	 *	@param	int	$count
	 *	@return	array
	 */
	public function read(int $count, bool $wordWrap = false)
	{
		if (!$wordWrap) {
			$phrase = \substr($this->text, $this->currentPos, $count);
			$this->currentPos += $count;
			return $phrase;
		}
		//
		if ($count > ($this->length - $this->currentPos)) {
			$phrase = \substr($this->text, $this->currentPos);
			$this->currentPos += \strlen($phrase);
			return $phrase;
		}
		//
		$phrase = \substr($this->text, $this->currentPos, $count + 1);
		$spaces = [" ","\r","\n","\t","\0"];
		$last = \substr($phrase, -2, 1);
		$extra = \substr($phrase, -1);
		//
		if (\in_array($last, $spaces, true)) {
			$this->currentPos += $count;
			return \substr($phrase, 0, -2);
		} elseif (\in_array($extra, $spaces, true)) {
			$this->currentPos += $count + 1;
			return \substr($phrase, 0, -1);
		} else {
			foreach ($spaces as $space) {
				if (($p = strrpos($phrase, $space)) !== FALSE) {
					$phrase = \substr($phrase, 0, $p);
					$this->currentPos += $p + 1;
					return $phrase;
				}
			}
			$this->currentPos += $count;
			return \substr($phrase, 0, -1);
		}
	}

	/**
	 *	Reads a single line from the inner string text
	 *
	 *	@return	string|bool
	 */
	public function readLine(bool $cleanEol = true)
	{
		if (($p = \strpos($this->text, PHP_EOL, $this->currentPos)) !== FALSE) {
			$line = \substr($this->text, $this->currentPos, $p);
			$this->currentPos += \strlen($line);
			//
			if ($cleanEol) {
				return \trim($line, "\n\r\0");
			} else {
				return $line;
			}
		}
		//
		$rest = \substr($this->text, $this->currentPos);
		$this->currentPos += \strlen($rest);
		//
		if ($cleanEol) {
			return \trim($rest, "\n\r\0");
		} else {
			return $rest;
		}
	}

	/**
	 *	Returns if the end of stream was reached or not
	 *
	 *	@return	bool
	 */
	public function eof()
	{
		return $this->currentPos >= $this->length;
	}

}


