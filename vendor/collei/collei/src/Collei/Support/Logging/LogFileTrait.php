<?php
namespace Collei\Support\Logging;

use DateTime;

/**
 *	Embodies a logging capability
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2022-10-09
 */
trait LogFileTrait
{
	/**
	 *	Logs autold issues
	 *
	 *	@param	mixed	$title
	 *	@param	mixed	$message
	 *	@param	mixed	$severity = null
	 *	@return	void
	 */
	protected static function log($title, $message, $severity = null)
	{
		static $timesCalled = 0;
		// obeys logging control flag
		if (!(PLAT_LOGGING['classloader'] ?? true)) {
			return;
		}
		//
		$file = PLAT_LOGS_GROUND
			. DIRECTORY_SEPARATOR
			. '.plat-autold-' . date('Ymd') . '.log';
		//
		if ($timesCalled == 0) {
			$line = "\r\n\r\n-------------------[ start of log at "
				. (new DateTime())->format('Y-m-d H:i:s.u')
				. ' ]--------------------';
			//
			file_put_contents($file, $line, FILE_APPEND);
		}
		//
		++$timesCalled;
		//
		$line = "\r\n"
			. '[' . (new DateTime())->format('Y-m-d H:i:s.u') . '] '
			. '[' . ($severity ?? 'common_log') . '] '
			. ($title . ' -> ' . $message);
		//
		file_put_contents($file, $line, FILE_APPEND);
	}

}

