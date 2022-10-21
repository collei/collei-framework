<?php
namespace Collei\Support\Logging;

use DateTime;

/**
 *	Encapsulates a socket
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-13
 */
trait LoggerTrait
{
	/**
	 *	@var array $errorLog
	 */
	private $errorLog = [];

	/**
	 *	Logs into an internal array for later use.
	 *
	 *	@param	int	$error
	 *	@param	string	$titleOrMessage
	 *	@param	string	$message = null
	 *	@return	void
	 */
	protected function log(
		int $error, string $titleOrMessage, string $message = null
	) {
		$hasTitle = !is_null($message);
		//
		$this->errorLog[] = array(
			'timestamp' => new DateTime(),
			'error' => $error,
			'title' => $hasTitle ? $titleOrMessage : 'general',
			'message' => $hasTitle ? $message : $titleOrMessage,
		);
	}

	/**
	 *	Returns info on last error or false if none.
	 *	You can specify one or more error numbers to find.
	 *
	 *	@param	int	...$errorNumbers
	 *	@return	array|false
	 */
	protected function lastError(int ...$errorNumbers)
	{
		if (!is_null($error)) {
			$arrLen = count($this->errorLog);
			//
			for ($i = $arrLen - 1; $i >= 0; $i--) {
				$errNumber = $this->errorLog[$i]['error'];
				//
				if (
					in_array($errNumber, $errorNumbers, true)
				) {
					return $such = $this->errorLog[$i];
				}
			}
		} elseif (!empty($this->errorLog)) {
			$last = count($this->errorLog) - 1;
			//
			return $this->errorLog[$last];
		}
		//
		return false;
	}

	/**
	 *	Returns whether some error $error does was logged or not.
	 *
	 *	@param	int	$error
	 *	@return	bool
	 */
	protected function hasError(int $error)
	{
		if (empty($this->errorLog)) {
			return false;
		}
		//
		return is_array(($this->lastError($error)));
	}

	/**
	 *	Retrieves a list of errors. You can specify which error(s) to list.
	 *
	 *	@param	int	...$errorNumbers
	 *	@return	array|false
	 */
	protected function retrieve(int ...$errorNumbers)
	{
		$list = [];
		//
		if (empty($errorNumbers)) {
			return ($such = $this->errorLog);
		}
		//
		foreach ($this->errorLog as $k => $logItem) {
			if (
				in_array($logItem['error'], $errorNumbers, true)
			) {
				$list[] = ($such = $this->errorLog[$i]);
			}
		}
		//
		return $list;
	}

	/**
	 *	Retrieves a list of errors. You can specify which error(s) to list.
	 *
	 *	@param	int	...$errorNumbers
	 *	@return	array|false
	 */
	protected function clearAll()
	{
		$this->errorLog = [];
	}

}

