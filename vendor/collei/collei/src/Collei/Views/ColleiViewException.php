<?php
namespace Collei\Views;

use Collei\Exceptions\ColleiException;

/**
 *	View exceptions
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-06-xx
 */
class ColleiViewException extends ColleiException
{
	/**
	 *	@var string $view_file
	 */
	private $view_file = '';

	/**
	 *	Builds and initializes a new instance of ColleiException
	 *
	 *	@param	string	$message
	 *	@param	string	$viewFile
	 *	@param	int		$line
	 */
	public function __construct(string $message = null, string $viewFile = null, int $line = 0)
	{
		parent::__construct($message, null, 0, null);

		$this->view_file = $viewFile ?? '';
		$this->file = $viewFile ?? $this->file;
		$this->line = $line;
	}

	/**
	 *	Returns the name of the view this Exception does refer to
	 *
	 *	@return	string
	 */
	public function getViewFile()
	{
		return $this->view_file;
	}

}

