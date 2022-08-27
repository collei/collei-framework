<?php
namespace Collei\Exceptions;

use Exception;
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
	public function __construct(string $message = null, string $title = null, int $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$classname = get_class($this);
		$this->title = $title ?? (($pos = strrpos($classname, '\\')) ? substr($classname, $pos + 1) : $apple);
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

