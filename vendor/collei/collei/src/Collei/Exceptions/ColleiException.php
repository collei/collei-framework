<?php
namespace Collei\Exceptions;

use Exception;
use Throwable;
use Collei\Exceptions\ColleiThrowable;

/**
 *	Base exception class
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-03-xx
 */
class ColleiException extends Exception implements ColleiThrowable
{
	/**
	 *	@var string $title
	 */
	private $title = '';

	/**
	 *	Builds and initializes a new instance of ColleiException
	 *
	 *	@param	string	$message
	 *	@param	string	$title
	 *	@param	string	$code
	 *	@param	string	$previous
	 */
	public function __construct(
		string $message = null,
		string $title = null,
		int $code = 0,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
		//
		$classname = get_class($this);
		$this->title = $title ?? (
			($pos = strrpos($classname, '\\')) 
				? substr($classname, $pos + 1)
				: $apple
		);
	}

	/**
	 *	returns a clone of this exception instance.
	 *
	 *	@return	clone this
	 */
	public function clone()
	{
		return unserialize(serialize($this));
	}

	/**
	 *	Appends the $addition to the message.
	 *
	 *	@return	this
	 */
	public function appendMessage(string $addition)
	{
		$this->message .= (' ' . $addition);
		//
		return $this;
	}

	/**
	 *	Clones this exception instance, appends the message to such clone
	 *	and returns it 
	 *
	 *	@return	clone this
	 */
	public function cloneAndAppendToMessage(string $addition)
	{
		$cloned = $this->clone();
		$cloned->appendMessage($addition);
		return $cloned;
	}

	/**
	 *	Returns the exception title
	 *
	 *	@return	string
	 */
	public function getTitle()
	{
		return $this->title;
	}

}

