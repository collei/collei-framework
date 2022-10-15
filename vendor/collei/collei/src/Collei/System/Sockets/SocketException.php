<?php
namespace Collei\System\Sockets;

use Collei\Exceptions\ColleiException;
use Throwable;

/**
 *	Exception on Collei sockets
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-13
 */
class SocketException extends ColleiException
{
	/**
	 *	Initializes an instance of.
	 *
	 *	@param	string		$message = null
	 *	@param	int			$code = null
	 *	@param	\Throwable	$previous = null
	 */
	public function __construct(
		string $message = null,
		int $code = null,
		Throwable $previous = null
	) {
		parent::__construct(
			$message, $message, ($code ?? -1), $previous
		);
	}

}

